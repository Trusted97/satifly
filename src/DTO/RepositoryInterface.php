<?php

namespace App\DTO;

interface RepositoryInterface
{
    /**
     * Get the repository name
     */
    public function getName(): string;

    /**
     * Set repository name.
     */
    public function setName(string $name): self;

    /**
     * Get unique identifier.
     */
    public function getId(): string;

    /**
     * Get the repository type
     */
    public function getType(): string;

    /**
     * Set repository type.
     */
    public function setType(string $type): self;

    /**
     * Get the repository host/url
     */
    public function getUrl(): string;

    /**
     * Set repository host/url.
     */
    public function setUrl(string $url): self;
}
