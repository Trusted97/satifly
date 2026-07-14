<?php

namespace App\DTO;

/**
 * Interface for repository data transfer objects.
 *
 * Defines the contract for repository properties and methods used
 * in Satis or similar package repository configurations.
 */
interface RepositoryInterface
{
    /**
     * Get the repository name.
     */
    public function getName(): string;

    /**
     * Set the repository name.
     */
    public function setName(string $name): self;

    /**
     * Get a unique identifier for the repository.
     */
    public function getId(): string;

    /**
     * Get the repository type (e.g., "vcs", "package").
     */
    public function getType(): string;

    /**
     * Set the repository type.
     */
    public function setType(string $type): self;

    /**
     * Get the repository URL or host.
     */
    public function getUrl(): string;

    /**
     * Set the repository URL or host.
     */
    public function setUrl(string $url): self;
}
