<?php

namespace App\Tests\DTO;

use App\DTO\Repository;
use PHPUnit\Framework\TestCase;

final class RepositoryTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $repo = new Repository('https://github.com/vendor/repo', 'vcs', 'my-repo');

        $this->assertSame('https://github.com/vendor/repo', $repo->getUrl());
        $this->assertSame('vcs', $repo->getType());
        $this->assertSame('my-repo', $repo->getName());
        $this->assertSame('dist', $repo->getInstallationSource());
    }

    public function testSetters(): void
    {
        $repo = new Repository();

        $repo->setUrl('https://github.com/other/repo')
            ->setType('composer')
            ->setName('other-repo')
            ->setInstallationSource('source');

        $this->assertSame('https://github.com/other/repo', $repo->getUrl());
        $this->assertSame('composer', $repo->getType());
        $this->assertSame('other-repo', $repo->getName());
        $this->assertSame('source', $repo->getInstallationSource());
    }

    public function testToString(): void
    {
        $repo = new Repository('https://github.com/vendor/repo');
        $this->assertSame('https://github.com/vendor/repo', (string) $repo);
    }

    public function testGetId(): void
    {
        $repo = new Repository('https://github.com/vendor/repo');
        $this->assertSame(\md5('https://github.com/vendor/repo'), $repo->getId());
    }
}
