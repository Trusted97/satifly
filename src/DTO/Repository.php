<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * Represents a repository configuration.
 *
 * Stores repository URL, type, name, and installation source.
 * Implements RepositoryInterface for Satis configuration usage.
 */
class Repository implements RepositoryInterface
{
    /**
     * Repository URL, e.g., "https://github.com/vendor/package.git"
     */
    private string $url;

    /**
     * Repository type, e.g., "vcs" or "package"
     */
    private string $type;

    /**
     * Repository name
     */
    private string $name;

    /**
     * Installation source (dist or source)
     */
    #[SerializedName('installation-source')]
    private string $installationSource = 'dist';

    /**
     * Constructor.
     *
     * @param string $url  Repository URL
     * @param string $type Repository type
     * @param string $name Repository name
     */
    public function __construct(string $url = '', string $type = 'vcs', string $name = '')
    {
        $this->url  = $url;
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * Get string representation of repository (URL)
     */
    public function __toString(): string
    {
        return $this->url;
    }

    /**
     * Get unique identifier for repository (MD5 of URL)
     */
    public function getId(): string
    {
        return \md5($this->getUrl());
    }

    /**
     * Get repository type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set repository type
     */
    public function setType(string $type): RepositoryInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get repository URL
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set repository URL
     */
    public function setUrl(string $url): RepositoryInterface
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get installation source
     */
    public function getInstallationSource(): string
    {
        return $this->installationSource;
    }

    /**
     * Set installation source
     */
    public function setInstallationSource(string $installationSource): RepositoryInterface
    {
        $this->installationSource = $installationSource;

        return $this;
    }

    /**
     * Get repository name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set repository name
     */
    public function setName(string $name): RepositoryInterface
    {
        $this->name = $name;

        return $this;
    }
}
