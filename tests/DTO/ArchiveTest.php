<?php

namespace App\Tests\DTO;

use App\DTO\Archive;
use PHPUnit\Framework\TestCase;

final class ArchiveTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $archive = new Archive();

        $this->assertSame('', $archive->getDirectory());
        $this->assertSame('', $archive->getFormat());
        $this->assertNull($archive->getAbsoluteDirectory());
        $this->assertTrue($archive->isSkipDev());
        $this->assertSame([], $archive->getWhitelist());
        $this->assertSame([], $archive->getBlacklist());
        $this->assertNull($archive->getPrefixUrl());
        $this->assertTrue($archive->isChecksum());
        $this->assertFalse($archive->isIgnoreFilters());
        $this->assertFalse($archive->isOverrideDistType());
        $this->assertTrue($archive->isRearchive());
    }

    public function testSettersAndGetters(): void
    {
        $archive = new Archive();

        $archive->setDirectory('build');
        $this->assertSame('build', $archive->getDirectory());

        $archive->setFormat('zip');
        $this->assertSame('zip', $archive->getFormat());

        $archive->setAbsoluteDirectory('/var/www/archive');
        $this->assertSame('/var/www/archive', $archive->getAbsoluteDirectory());

        $archive->setSkipDev(false);
        $this->assertFalse($archive->isSkipDev());

        $whitelist = ['vendor/package1', 'vendor/package2'];
        $archive->setWhitelist($whitelist);
        $this->assertSame($whitelist, $archive->getWhitelist());

        $blacklist = ['vendor/bad-package'];
        $archive->setBlacklist($blacklist);
        $this->assertSame($blacklist, $archive->getBlacklist());

        $archive->setPrefixUrl('https://example.com/');
        $this->assertSame('https://example.com/', $archive->getPrefixUrl());

        $archive->setChecksum(false);
        $this->assertFalse($archive->isChecksum());

        $archive->setIgnoreFilters(true);
        $this->assertTrue($archive->isIgnoreFilters());

        $archive->setOverrideDistType(true);
        $this->assertTrue($archive->isOverrideDistType());

        $archive->setRearchive(false);
        $this->assertFalse($archive->isRearchive());
    }

    public function testSetDirectoryAndFormatWithNull(): void
    {
        $archive = new Archive();

        // Should not overwrite default values if null is passed
        $archive->setDirectory(null);
        $archive->setFormat(null);

        $this->assertSame('', $archive->getDirectory());
        $this->assertSame('', $archive->getFormat());
    }
}
