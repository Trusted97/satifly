<?php

namespace App\Tests\Integration;

use App\DTO\Archive;
use App\DTO\Configuration;
use App\Persister\ConfigurationNormalizer;
use App\Persister\FilePersister;
use App\Persister\JsonPersister;
use Composer\Satis\Console\Application;
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
    use \App\Tests\Traits\TempFilesystemTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempSetup('satifly-build');
        $this->configureSatisTestEnv();
    }

    protected function tearDown(): void
    {
        $this->tempTearDown();
        parent::tearDown();
    }

    public function testBuildFailsWhenConfigIsMissing(): void
    {
        $configFile  = $this->tempPath('satis.json');
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
        $configFile = $this->writeTempFile('satis.json', (string) \file_get_contents(__DIR__ . '/../fixtures/satis-minimal.json'));

        $outputDir = $this->tempPath('output');
        (new Filesystem())->mkdir($outputDir);

        $input       = $this->createInput($configFile, $outputDir);
        $output      = $this->createOutput();
        $application = $this->createSatisApplication();

        $exitCode = $application->run($input, $output);

        self::assertSame(0, $exitCode, 'Expected exit code 0 for minimal config build.');
        self::assertFileExists($outputDir . '/index.html', 'index.html must be generated.');
        self::assertFileExists($outputDir . '/packages.json', 'packages.json must be generated.');
        self::assertDirectoryExists($outputDir . '/include', 'include directory must exist.');

        self::assertNotEmpty(\glob($outputDir . '/include/*') ?: [], 'include directory must contain files.');
    }

    public function testBuildWithDefaultFormConfigSucceeds(): void
    {
        $configFile = $this->tempPath('satis.json');

        self::bootKernel()->getContainer();

        $serializer    = $this->createSerializer();
        $filePersister = new FilePersister(new Filesystem(), $configFile, $this->tempPath('satis'));
        $persister     = new JsonPersister($filePersister, $serializer, Configuration::class);

        $configuration = new Configuration();
        $archive       = new Archive();
        $archive->setFormat('zip');
        $configuration->setArchive($archive);

        $persister->flush($configuration);

        $outputDir = $this->tempPath('output');
        (new Filesystem())->mkdir($outputDir);

        $input       = $this->createInput($configFile, $outputDir);
        $output      = $this->createOutput();
        $application = $this->createSatisApplication();

        try {
            $exitCode = $application->run($input, $output);
            self::assertSame(0, $exitCode, 'Expected exit code 0 for default form config build.');
        } catch (AssertionFailedError $error) {
            echo (string) \file_get_contents($configFile);
            echo $output->fetch();
            throw $error;
        }

        self::assertFileExists($outputDir . '/index.html', 'index.html must be generated.');
        self::assertFileExists($outputDir . '/packages.json', 'packages.json must be generated.');
        self::assertDirectoryExists($outputDir . '/include', 'include directory must exist.');

        self::assertNotEmpty(\glob($outputDir . '/include/*') ?: [], 'include directory must contain files.');
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
