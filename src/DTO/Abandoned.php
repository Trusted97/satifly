<?php

namespace App\DTO;

class Abandoned
{
    private string $package;

    private ?string $replacement;

    public function __construct(string $package, ?string $replacement)
    {
        $this->package     = $package;
        $this->replacement = $replacement;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getReplacement(): ?string
    {
        return $this->replacement;
    }
}
