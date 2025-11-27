<?php

namespace App\Tests\Persister;

use App\DTO\Configuration;
use App\DTO\PackageConstraint;
use App\DTO\Repository;
use App\DTO\RepositoryInterface;
use App\Persister\ConfigurationNormalizer;
use App\Persister\FilePersister;
use App\Persister\JsonPersister;
use App\Tests\Traits\SchemaValidatorTrait;
use App\Tests\Traits\VfsTrait;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class FilePersisterTest extends KernelTestCase
{
    use SchemaValidatorTrait;
    use VfsTrait;

    private ?FilePersister $persister = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vfsSetup();

        $this->persister = new FilePersister(
            new Filesystem(),
            $this->vfsRoot->url() . '/satis.json',
            $this->vfsRoot->url()
        );
    }

    protected function tearDown(): void
    {
        $this->vfsTearDown();
        $this->persister = null;
        parent::tearDown();
    }

    public function testFlushTruncatesFileCorrectly(): void
    {
        $config = [
            'name'         => 'test',
            'homepage'     => 'http://localhost',
            'repositories' => [
                ['type' => 'git', 'url' => 'https://github.com/ludofleury/satisfy.git', 'name' => 'ludofleury/satisfy'],
            ],
            'require-all' => true,
        ];

        $content = \json_encode($config);
        $this->persister->flush($content);

        /** @var vfsStreamFile $configFile */
        $configFile = $this->vfsRoot->getChild('satis.json');

        self::assertStringEqualsFile($configFile->url(), $content, 'File content must match flushed content.');
        self::assertSame($content, $this->persister->load(), 'Loaded content must match flushed content.');

        $this->validateSchema(\json_decode($configFile->getContent()), $this->getSatisSchema());

        // truncate repositories
        $config['repositories'] = [];
        $content                = \json_encode($config);
        $this->persister->flush($content);

        self::assertStringEqualsFile($configFile->url(), $content, 'After truncation, file content must match.');
        self::assertSame($content, $this->persister->load(), 'Loaded content must match truncated content.');
    }

    public function testJsonPersisterNormalizationWorks(): void
    {
        $file = new vfsStreamFile('satis.json');
        $file->setContent(\file_get_contents(__DIR__ . '/../fixtures/satis-full.json'));
        $this->vfsRoot->addChild($file);

        self::bootKernel();

        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $serializer           = new Serializer(
            [
                new ConfigurationNormalizer(),
                new ObjectNormalizer(
                    classMetadataFactory: $classMetadataFactory,
                    nameConverter: new MetadataAwareNameConverter(
                        $classMetadataFactory,
                        new CamelCaseToSnakeCaseNameConverter()
                    ),
                    propertyTypeExtractor: new PropertyInfoExtractor([], [
                        new PhpDocExtractor(),
                        new ReflectionExtractor(),
                    ])
                ),
            ],
            [new JsonEncoder()]
        );

        $persister = new JsonPersister(
            $this->persister,
            $serializer,
            Configuration::class
        );

        $config = $persister->load();

        // validate require
        $require = $config->getRequire();
        self::assertIsArray($require, 'Require must be an array.');
        self::assertCount(1, $require, 'Require array must contain one element.');

        // validate repositories
        $repositories = $config->getRepositories();
        self::assertCount(1, $repositories, 'Repositories must contain one element.');
        self::assertInstanceOf(RepositoryInterface::class, $repositories->current(), 'Repository must implement RepositoryInterface.');

        // append additional repository
        $repositories->append(new Repository(url: 'http://localhost', name: 'funny/test'));
        $config->setRepositories($repositories);

        // modify existing require and add new
        $constraint = \reset($require);
        $constraint->setConstraint('^2.0');
        $config->setRequire([
            $constraint,
            new PackageConstraint('psr/log', '^1.0'),
        ]);

        // add minimum stability per package
        $config->addMinimumStabilityPerPackage('phpunit/phpunit', 'alpha');

        $persister->flush($config);

        $config = $persister->load();

        self::assertCount(2, $config->getRequire(), 'Require array must contain two elements after modification.');
        self::assertCount(2, $config->getRepositories(), 'Repositories must contain two elements after modification.');
        self::assertInstanceOf(RepositoryInterface::class, $config->getRepositories()->current(), 'Repository must implement RepositoryInterface.');
    }
}
