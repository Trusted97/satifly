<?php

namespace App\Tests\Service;

use App\DTO\Configuration;
use App\DTO\Repository;
use App\Exception\MissingConfigException;
use App\Persister\JsonPersister;
use App\Service\RepositoryManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

final class RepositoryManagerTest extends TestCase
{
    public function testGetConfigFallsBackToEmptyConfigurationAndCachesIt(): void
    {
        $lockFactory = $this->createMock(LockFactory::class);
        $lock        = $this->createMock(SharedLockInterface::class);
        $lockFactory->expects($this->once())
            ->method('createLock')
            ->with('satis')
            ->willReturn($lock);

        $persister = $this->createMock(JsonPersister::class);
        $persister->expects($this->once())
            ->method('load')
            ->willThrowException(new MissingConfigException('missing'));

        $manager = new RepositoryManager($lockFactory, $persister);

        $config = $manager->getConfig();

        self::assertInstanceOf(Configuration::class, $config);
        self::assertSame($config, $manager->getConfig(), 'Config must be cached after first load.');
    }

    public function testAddPersistsRepositoryUnderLock(): void
    {
        $lockFactory = $this->createMock(LockFactory::class);
        $lock        = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())
            ->method('acquire')
            ->willReturn(true);
        $lock->expects($this->once())
            ->method('release');

        $lockFactory->expects($this->once())
            ->method('createLock')
            ->with('satis')
            ->willReturn($lock);

        $persister = $this->createMock(JsonPersister::class);
        $persister->expects($this->once())
            ->method('load')
            ->willReturn(new Configuration());
        $persister->expects($this->once())
            ->method('flush')
            ->with($this->callback(static function (Configuration $config): bool {
                $repositories = $config->getRepositories();

                return 1 === \count($repositories)
                    && $repositories->offsetExists(md5('https://example.com/repo.git'))
                    && 'https://example.com/repo.git' === $repositories[md5('https://example.com/repo.git')]->getUrl();
            }));

        $manager = new RepositoryManager($lockFactory, $persister);
        $manager->add(new Repository('https://example.com/repo.git', 'git', 'vendor/repo'));

        self::assertCount(1, $manager->getRepositories());
    }

    public function testFindByUrlMatchesDifferentUrlShapes(): void
    {
        $manager = $this->managerWithRepositories([
            new Repository('https://example.com/vendor/package.git', 'git', 'package'),
            new Repository('https://git.example.org/tools/app', 'git', 'app'),
        ]);

        self::assertSame(
            'https://example.com/vendor/package.git',
            $manager->findByUrl('https://example.com/vendor/package.git/')->getUrl()
        );

        self::assertSame(
            'https://git.example.org/tools/app',
            $manager->findByUrl('https://git.example.org')->getUrl()
        );

        self::assertSame(
            'https://git.example.org/tools/app',
            $manager->findByUrl('tools/app')->getUrl()
        );
    }

    public function testAcquireLockThrowsWhenLockCannotBeAcquired(): void
    {
        $lockFactory = $this->createMock(LockFactory::class);
        $lock        = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())
            ->method('acquire')
            ->willReturn(false);

        $lockFactory->expects($this->once())
            ->method('createLock')
            ->with('satis')
            ->willReturn($lock);

        $manager = new RepositoryManager($lockFactory, $this->createMock(JsonPersister::class));

        $this->expectException(IOException::class);
        $manager->acquireLock();
    }

    private function managerWithRepositories(array $repositories): RepositoryManager
    {
        $lockFactory = $this->createMock(LockFactory::class);
        $lock        = $this->createMock(SharedLockInterface::class);
        $lock->method('acquire')->willReturn(true);
        $lock->method('release');
        $lockFactory->method('createLock')->willReturn($lock);

        $config = new Configuration();
        foreach ($repositories as $repository) {
            $config->getRepositories()->offsetSet($repository->getId(), $repository);
        }

        $persister = $this->createMock(JsonPersister::class);
        $persister->expects($this->once())
            ->method('load')
            ->willReturn($config);
        $persister->expects($this->never())
            ->method('flush');

        return new RepositoryManager($lockFactory, $persister);
    }
}
