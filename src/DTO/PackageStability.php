<?php

namespace App\DTO;

class PackageStability
{
    private string $package;

    private string $stability;

    public function __construct(string $package, string $stability)
    {
        $this->package   = $package;
        $this->stability = $stability;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getStability(): string
    {
        return $this->stability;
    }
}
