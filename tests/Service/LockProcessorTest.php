<?php

namespace App\Tests\Service;

use App\DTO\Repository;
use App\Service\LockProcessor;
use App\Service\RepositoryManager;
use PHPUnit\Framework\TestCase;

final class LockProcessorTest extends TestCase
{
    public function testProcessFileAddsRepositories(): void
    {
        $json = <<<JSON
{
    "packages": [
        {
            "name": "vendor/package1",
            "source": { "url": "https://example.com/repo1.git", "type": "git" }
        }
    ],
    "packages-dev": [
        {
            "name": "vendor/package2",
            "source": { "url": "https://example.com/repo2.git", "type": "git" }
        }
    ]
}
JSON;

        $tmpFile = \tmpfile();
        \fwrite($tmpFile, $json);
        $path = \stream_get_meta_data($tmpFile)['uri'];
        $file = new \SplFileObject($path);

        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('addAll')
            ->with($this->callback(function ($repositories) {
                return 2 === \count($repositories)
                    && $repositories[0] instanceof Repository
                    && 'https://example.com/repo1.git' === $repositories[0]->getUrl()
                    && 'git' === $repositories[0]->getType()
                    && $repositories[1] instanceof Repository
                    && 'https://example.com/repo2.git' === $repositories[1]->getUrl()
                    && 'git' === $repositories[1]->getType();
            }));

        $processor = new LockProcessor($manager);
        $processor->processFile($file);

        \fclose($tmpFile);
    }

    public function testGetRepositoriesFiltersInvalidPackages(): void
    {
        $manager   = $this->createMock(RepositoryManager::class);
        $processor = new LockProcessor($manager);

        $packages = [
            (object) [
                'name'   => 'vendor/valid',
                'source' => (object) ['url' => 'https://example.com/repo.git', 'type' => 'git'],
            ],
            (object) [
                'name'   => 'vendor/invalid',
                'source' => (object) ['url' => '', 'type' => ''],
            ],
            (object) [
                'name' => 'vendor/nosource',
            ],
        ];

        $method       = new \ReflectionMethod(LockProcessor::class, 'getRepositories');
        $repositories = $method->invoke($processor, $packages);

        $this->assertCount(1, $repositories);
        $this->assertSame('https://example.com/repo.git', $repositories[0]->getUrl());
        $this->assertSame('git', $repositories[0]->getType());
    }
}
