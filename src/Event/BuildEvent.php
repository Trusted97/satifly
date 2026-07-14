<?php

namespace App\Event;

use App\DTO\RepositoryInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event triggered when building a repository in Satis.
 *
 * Carries an optional repository and allows tracking build status.
 */
class BuildEvent extends Event
{
    /**
     * Event name constant.
     */
    public const string NAME = 'satis_build';

    /**
     * The repository associated with this build event.
     */
    private ?RepositoryInterface $repository;

    /**
     * Build status code (optional).
     */
    private ?int $status = null;

    /**
     * Constructor.
     *
     * @param RepositoryInterface|null $repository Optional repository
     */
    public function __construct(?RepositoryInterface $repository = null)
    {
        $this->repository = $repository;
    }

    /**
     * Get the repository associated with this event.
     */
    public function getRepository(): ?RepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Get the build status code.
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * Set the build status code.
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
}
