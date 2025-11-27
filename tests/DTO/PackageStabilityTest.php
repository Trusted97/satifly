<?php

namespace App\Tests\DTO;

use App\DTO\PackageStability;
use PHPUnit\Framework\TestCase;

final class PackageStabilityTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $stability = new PackageStability('vendor/package', 'dev');

        // Test initial state
        $this->assertSame('vendor/package', $stability->getPackage());
        $this->assertSame('dev', $stability->getStability());

        // Test setters
        $stability->setPackage('vendor/updated');
        $stability->setStability('stable');

        // Verify updated values
        $this->assertSame('vendor/updated', $stability->getPackage());
        $this->assertSame('stable', $stability->getStability());

        // Test null does not overwrite
        $stability->setPackage(null);
        $stability->setStability(null);

        $this->assertSame('vendor/updated', $stability->getPackage());
        $this->assertSame('stable', $stability->getStability());
    }
}
