<?php

namespace App\Tests\Manager;

use App\DTO\Configuration;
use App\Persister\JsonPersister;
use App\Service\RepositoryManager;
use App\Tests\Traits\SchemaValidatorTrait;
use App\Tests\Traits\VfsTrait;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

class ManagerConfigValidatorTest extends TestCase
{
    use ProphecyTrait;
    use SchemaValidatorTrait;
    use VfsTrait;

    /** @var vfsStreamFile */
    protected $config;

    protected function setUp(): void
    {
        $this->vfsSetup();
        $this->vfsRoot->addChild($this->config = new vfsStreamFile('satis.json'));
    }

    protected function tearDown(): void
    {
        $this->vfsTearDown();
    }

    /**
     * @dataProvider configFileProvider
     */
    public function testConfigIsMatchingSatisSchema($configFilename): void
    {
        $this->assertTrue(\copy($configFilename, $this->config->url()));
        $persister = $this->prophesize(JsonPersister::class);
        $persister
            ->load()
            ->willReturn(new Configuration());
        $persister
            ->flush(Argument::type(Configuration::class))
            ->shouldBeCalled();

        $lockFactory = new LockFactory(new FlockStore());
        /** @var RepositoryManager $manager */
        $manager = new RepositoryManager($lockFactory, $persister->reveal());
        $manager->addAll([]);

        $this->validateSchema(\json_decode($this->config->getContent()), $this->getSatisSchema());
        $this->assertJsonFileEqualsJsonFile($configFilename, $this->config->url());
    }

    public static function configFileProvider(): array
    {
        return [
            [__DIR__ . '/../fixtures/satis-minimal.json'],
            [__DIR__ . '/../fixtures/satis-full.json'],
        ];
    }
}
