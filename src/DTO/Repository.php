<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

class Repository implements RepositoryInterface
{
    private string $url;

    private string $type;

    private string $name;

    #[SerializedName('installation-source')]
    private string $installationSource = 'dist';

    public function __construct(string $url = '', string $type = 'vcs', string $name = '')
    {
        $this->url  = $url;
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * Get the string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->url;
    }

    /**
     * Get identifier
     */
    public function getId(): string
    {
        return \md5($this->getUrl());
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): RepositoryInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): RepositoryInterface
    {
        $this->url = $url;

        return $this;
    }

    public function getInstallationSource(): string
    {
        return $this->installationSource;
    }

    public function setInstallationSource(string $installationSource): RepositoryInterface
    {
        $this->installationSource = $installationSource;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): RepositoryInterface
    {
        $this->name = $name;

        return $this;
    }
}
