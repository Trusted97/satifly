<?php

namespace App\Tests\DTO;

use App\DTO\Abandoned;
use PHPUnit\Framework\TestCase;

final class AbandonedTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $package     = 'vendor/package';
        $replacement = 'vendor/replacement';

        $abandoned = new Abandoned($package, $replacement);

        $this->assertSame($package, $abandoned->getPackage(), 'Package getter should return constructor value.');
        $this->assertSame($replacement, $abandoned->getReplacement(), 'Replacement getter should return constructor value.');
    }

    public function testNullableReplacement(): void
    {
        $package     = 'vendor/package';
        $replacement = null;

        $abandoned = new Abandoned($package, $replacement);

        $this->assertSame($package, $abandoned->getPackage());
        $this->assertNull($abandoned->getReplacement(), 'Replacement can be null.');
    }

    public function testSetters(): void
    {
        $abandoned = new Abandoned('initial/package', null);

        $abandoned->setPackage('new/package');
        $abandoned->setReplacement('new/replacement');

        $this->assertSame('new/package', $abandoned->getPackage());
        $this->assertSame('new/replacement', $abandoned->getReplacement());

        // Test setting replacement to null
        $abandoned->setReplacement(null);
        $this->assertNull($abandoned->getReplacement());
    }
}
