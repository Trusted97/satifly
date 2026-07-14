<?php

namespace App\DTO;

/**
 * Represents a package version constraint.
 *
 * This class is used to store a package name along with its version
 * constraint, typically used in Satis configurations for required packages.
 */
class PackageConstraint
{
    /**
     * The name of the package, e.g., "vendor/package"
     */
    private string $package;

    /**
     * The version constraint for the package, e.g., "^1.0", ">=2.0 <3.0"
     */
    private string $constraint;

    /**
     * Constructor.
     *
     * @param string $package    Package name
     * @param string $constraint Version constraint
     */
    public function __construct(string $package, string $constraint)
    {
        $this->package    = $package;
        $this->constraint = $constraint;
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
     */
    public function setPackage(string $package): void
    {
        $this->package = $package;
    }

    /**
     * Get the version constraint.
     */
    public function getConstraint(): string
    {
        return $this->constraint;
    }

    /**
     * Set the version constraint.
     *
     * Returns `$this` for fluent method chaining.
     */
    public function setConstraint(string $constraint): static
    {
        $this->constraint = $constraint;

        return $this;
    }
}
