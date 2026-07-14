<?php

namespace App\DTO;

/**
 * Data Transfer Object representing an abandoned package.
 *
 * This DTO is used to store information about a package that has been
 * abandoned and optionally provide a replacement package suggestion.
 */
class Abandoned
{
    /**
     * The name of the abandoned package.
     */
    private string $package;

    /**
     * The suggested replacement package, if any.
     */
    private ?string $replacement;

    /**
     * Constructor.
     *
     * @param string      $package     The abandoned package name
     * @param string|null $replacement Optional suggested replacement
     */
    public function __construct(string $package, ?string $replacement)
    {
        $this->package     = $package;
        $this->replacement = $replacement;
    }

    /**
     * Get the package name.
     */
    public function getPackage(): string
    {
        return $this->package;
    }

    /**
     * Get the suggested replacement package.
     */
    public function getReplacement(): ?string
    {
        return $this->replacement;
    }

    /**
     * Set the package name.
     */
    public function setPackage(string $package): void
    {
        $this->package = $package;
    }

    /**
     * Set the suggested replacement package.
     */
    public function setReplacement(?string $replacement): void
    {
        $this->replacement = $replacement;
    }
}
