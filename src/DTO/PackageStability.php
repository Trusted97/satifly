<?php

namespace App\DTO;

/**
 * Represents the stability requirement of a specific package.
 *
 * Used to define the minimum stability for individual packages
 * in a Satis configuration.
 */
class PackageStability
{
    /**
     * The package name, e.g., "vendor/package".
     */
    private string $package;

    /**
     * The stability level for the package, e.g., "dev", "stable".
     */
    private string $stability;

    /**
     * Constructor.
     *
     * @param string $package   Name of the package
     * @param string $stability Minimum stability requirement
     */
    public function __construct(string $package, string $stability)
    {
        $this->package   = $package;
        $this->stability = $stability;
    }

    /**
     * Get the package name.
     */
    public function getPackage(): string
    {
        return $this->package;
    }

    /**
     * Set the package name.
     *
     * Ignores null values.
     */
    public function setPackage(?string $package): void
    {
        if (null !== $package) {
            $this->package = $package;
        }
    }

    /**
     * Get the stability level.
     */
    public function getStability(): string
    {
        return $this->stability;
    }

    /**
     * Set the stability level.
     *
     * Ignores null values.
     */
    public function setStability(?string $stability): void
    {
        if (null !== $stability) {
            $this->stability = $stability;
        }
    }
}
