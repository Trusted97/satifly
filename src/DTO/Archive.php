<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * Archive Configuration class
 *
 * Represents the "archive" section in a Satis configuration file.
 */
class Archive
{
    private string $directory = '';

    private string $format = '';

    #[SerializedName('absolute-directory')]
    private ?string $absoluteDirectory = null;

    #[SerializedName('skip-dev')]
    private bool $skipDev = true;

    private array $whitelist = [];

    private array $blacklist = [];

    #[SerializedName('prefix-url')]
    private ?string $prefixUrl = null;

    private bool $checksum = true;

    #[SerializedName('ignore-filters')]
    private bool $ignoreFilters = false;

    #[SerializedName('override-dist-type')]
    private bool $overrideDistType = false;

    private bool $rearchive = true;

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function setDirectory(?string $directory): void
    {
        if (null !== $directory) {
            $this->directory = $directory;
        }
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(?string $format): void
    {
        if (null !== $format) {
            $this->format = $format;
        }
    }

    public function getAbsoluteDirectory(): ?string
    {
        return $this->absoluteDirectory;
    }

    public function setAbsoluteDirectory(?string $absoluteDirectory): void
    {
        $this->absoluteDirectory = $absoluteDirectory;
    }

    public function isSkipDev(): bool
    {
        return $this->skipDev;
    }

    public function setSkipDev(bool $skipDev): void
    {
        $this->skipDev = $skipDev;
    }

    public function getWhitelist(): array
    {
        return $this->whitelist;
    }

    public function setWhitelist(array $whitelist): void
    {
        $this->whitelist = $whitelist;
    }

    public function getBlacklist(): array
    {
        return $this->blacklist;
    }

    public function setBlacklist(array $blacklist): void
    {
        $this->blacklist = $blacklist;
    }

    public function getPrefixUrl(): ?string
    {
        return $this->prefixUrl;
    }

    public function setPrefixUrl(?string $prefixUrl): void
    {
        $this->prefixUrl = $prefixUrl;
    }

    public function isChecksum(): bool
    {
        return $this->checksum;
    }

    public function setChecksum(bool $checksum): void
    {
        $this->checksum = $checksum;
    }

    public function isIgnoreFilters(): bool
    {
        return $this->ignoreFilters;
    }

    public function setIgnoreFilters(bool $ignoreFilters): void
    {
        $this->ignoreFilters = $ignoreFilters;
    }

    public function isOverrideDistType(): bool
    {
        return $this->overrideDistType;
    }

    public function setOverrideDistType(bool $overrideDistType): void
    {
        $this->overrideDistType = $overrideDistType;
    }

    public function isRearchive(): bool
    {
        return $this->rearchive;
    }

    public function setRearchive(bool $rearchive): void
    {
        $this->rearchive = $rearchive;
    }
}
