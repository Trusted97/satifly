<?php

namespace App\Tests\Integration\Composer\Satis\Command;

use App\DTO\Archive;
use App\DTO\Configuration;
use App\Persister\ConfigurationNormalizer;
use App\Persister\FilePersister;
use App\Persister\JsonPersister;
use Composer\Satis\Console\Application;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
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

final class BuildCommandTest extends KernelTestCase
{
    private ?vfsStreamDirectory $vfsRoot = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vfsRoot = vfsStream::setup();
    }

    protected function tearDown(): void
    {
        $this->vfsRoot = null;
        parent::tearDown();
    }

    public function testBuildFailsWhenConfigIsMissing(): void
    {
        $configFile  = $this->vfsRoot->url() . '/satis.json';
        $input       = $this->createInput($configFile);
        $output      = $this->createOutput();
        $application = $this->createSatisApplication();

        $exitCode = $application->run($input, $output);

        self::assertSame(1, $exitCode, 'Expected exit code 1 when config file is missing.');
        self::assertStringStartsWith(
            'File not found',
            $output->fetch(),
            'Expected output to indicate missing config file.'
        );
    }

    public function testBuildWithMinimalConfigSucceeds(): void
    {
        $configFile = new vfsStreamFile('satis.json');
        $configFile->setContent(\file_get_contents(__DIR__ . '/../fixtures/satis-minimal.json'));
        $this->vfsRoot->addChild($configFile);

        $outputDir = new vfsStreamDirectory('output');
        $this->vfsRoot->addChild($outputDir);

        $input       = $this->createInput($configFile->url(), $outputDir->url());
        $output      = $this->createOutput();
        $application = $this->createSatisApplication();

        $exitCode = $application->run($input, $output);

        self::assertSame(0, $exitCode, 'Expected exit code 0 for minimal config build.');
        self::assertTrue($outputDir->hasChild('index.html'), 'index.html must be generated.');
        self::assertTrue($outputDir->hasChild('packages.json'), 'packages.json must be generated.');
        self::assertTrue($outputDir->hasChild('include'), 'include directory must exist.');

        /** @var vfsStreamDirectory $includeDir */
        $includeDir = $outputDir->getChild('include');
        self::assertTrue($includeDir->hasChildren(), 'include directory must contain files.');
    }

    public function testBuildWithDefaultFormConfigSucceeds(): void
    {
        $configFile = new vfsStreamFile('satis.json');
        $this->vfsRoot->addChild($configFile);

        self::bootKernel()->getContainer();

        $serializer    = $this->createSerializer();
        $filePersister = new FilePersister(new Filesystem(), $configFile->url(), $this->vfsRoot->url());
        $persister     = new JsonPersister($filePersister, $serializer, Configuration::class);

        $configuration = new Configuration();
        $archive       = new Archive();
        $archive->setFormat('zip');
        $configuration->setArchive($archive);

        $persister->flush($configuration);

        $outputDir = new vfsStreamDirectory('output');
        $this->vfsRoot->addChild($outputDir);

        $input       = $this->createInput($configFile->url(), $outputDir->url());
        $output      = $this->createOutput();
        $application = $this->createSatisApplication();

        try {
            $exitCode = $application->run($input, $output);
            self::assertSame(0, $exitCode, 'Expected exit code 0 for default form config build.');
        } catch (AssertionFailedError $error) {
            echo $configFile->getContent();
            echo $output->fetch();
            throw $error;
        }

        self::assertTrue($outputDir->hasChild('index.html'), 'index.html must be generated.');
        self::assertTrue($outputDir->hasChild('packages.json'), 'packages.json must be generated.');
        self::assertTrue($outputDir->hasChild('include'), 'include directory must exist.');

        /** @var vfsStreamDirectory $includeDir */
        $includeDir = $outputDir->getChild('include');
        self::assertTrue($includeDir->hasChildren(), 'include directory must contain files.');
    }

    private function createSatisApplication(): Application
    {
        $application = new Application();
        $application->setAutoExit(false);

        return $application;
    }

    private function createOutput(): BufferedOutput
    {
        return new BufferedOutput();
    }

    private function createInput(string $file, string $outputDir = ''): ArrayInput
    {
        return new ArrayInput([
            'command'    => 'build',
            'file'       => $file,
            'output-dir' => $outputDir,
            '-vv',
        ]);
    }

    private function createSerializer(): Serializer
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());

        return new Serializer([
            new ConfigurationNormalizer(),
            new ObjectNormalizer(
                classMetadataFactory: $classMetadataFactory,
                nameConverter: new MetadataAwareNameConverter(
                    $classMetadataFactory,
                    new CamelCaseToSnakeCaseNameConverter()
                ),
                propertyTypeExtractor: new PropertyInfoExtractor(
                    [],
                    [new PhpDocExtractor(), new ReflectionExtractor()]
                )
            ),
        ], [new JsonEncoder()]);
    }
}
