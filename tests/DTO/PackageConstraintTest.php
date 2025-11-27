<?php

namespace App\Tests\DTO;

use App\DTO\PackageConstraint;
use PHPUnit\Framework\TestCase;

final class PackageConstraintTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $packageConstraint = new PackageConstraint('vendor/package', '^1.0');

        // Test initial state
        $this->assertSame('vendor/package', $packageConstraint->getPackage());
        $this->assertSame('^1.0', $packageConstraint->getConstraint());

        // Test setters
        $packageConstraint->setPackage('vendor/updated');
        $packageConstraint->setConstraint('^2.0');

        // Verify updated values
        $this->assertSame('vendor/updated', $packageConstraint->getPackage());
        $this->assertSame('^2.0', $packageConstraint->getConstraint());
    }
}
