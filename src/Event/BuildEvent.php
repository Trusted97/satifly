<?php

namespace App\Event;

use App\DTO\RepositoryInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class BuildEvent extends Event
{
    public const string NAME = 'satis_build';

    private ?RepositoryInterface $repository;

    private ?int $status = null;

    public function __construct(?RepositoryInterface $repository = null)
    {
        $this->repository = $repository;
    }

    public function getRepository(): ?RepositoryInterface
    {
        return $this->repository;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
}
