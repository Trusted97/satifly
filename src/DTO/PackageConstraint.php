<?php

namespace App\DTO;

class PackageConstraint
{
    private string $package;

    private string $constraint;

    public function __construct(string $package, string $constraint)
    {
        $this->package    = $package;
        $this->constraint = $constraint;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function setPackage(string $package): void
    {
        $this->package = $package;
    }

    public function getConstraint(): string
    {
        return $this->constraint;
    }

    public function setConstraint(string $constraint): static
    {
        $this->constraint = $constraint;

        return $this;
    }
}
