<?php

namespace App\Tests\Manager;

use App\DTO\Configuration;
use App\Persister\JsonPersister;
use App\Service\RepositoryManager;
use App\Tests\Traits\SchemaValidatorTrait;
use App\Tests\Traits\VfsTrait;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

final class ManagerConfigValidatorTest extends TestCase
{
    use ProphecyTrait;
    use SchemaValidatorTrait;
    use VfsTrait;

    private vfsStreamFile $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vfsSetup();
        $this->vfsRoot->addChild($this->config = new vfsStreamFile('satis.json'));
    }

    protected function tearDown(): void
    {
        $this->vfsTearDown();
        parent::tearDown();
    }

    #[DataProvider(methodName: 'configFileProvider')]
    public function testConfigIsMatchingSatisSchema(string $configFilePath): void
    {
        // copy fixture into vfs
        $copied = \copy($configFilePath, $this->config->url());
        self::assertTrue($copied, 'Fixture file must be copied into virtual filesystem.');

        // create a Prophecy for JsonPersister
        $persister = $this->prophesize(JsonPersister::class);
        $persister->load()->willReturn(new Configuration())->shouldBeCalled();
        $persister->flush(Argument::type(Configuration::class))->shouldBeCalled();

        // instantiate RepositoryManager with LockFactory and persister mock
        $lockFactory = new LockFactory(new FlockStore());
        $manager = new RepositoryManager($lockFactory, $persister->reveal());

        // call addAll to simulate repository addition
        $manager->addAll([]);

        // validate JSON against Satis schema
        $decodedConfig = \json_decode($this->config->getContent());
        $this->validateSchema($decodedConfig, $this->getSatisSchema());

        // assert virtual file still matches fixture
        self::assertJsonFileEqualsJsonFile(
            $configFilePath,
            $this->config->url(),
            'Generated JSON must match fixture.'
        );
    }

    public static function configFileProvider(): array
    {
        return [
            [__DIR__ . '/../fixtures/satis-minimal.json'],
            [__DIR__ . '/../fixtures/satis-full.json'],
        ];
    }
}
