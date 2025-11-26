<?php

namespace App\Service;

use App\DTO\Configuration;
use App\DTO\RepositoryInterface;
use App\Exception\MissingConfigException;
use App\Persister\JsonPersister;
use App\Persister\PersisterInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class RepositoryManager
{
    private LockInterface $lock;

    private PersisterInterface $persisterInterface;

    private ?Configuration $configuration = null;

    /**
     * Constructor
     */
    public function __construct(LockFactory $satisLockFactory, JsonPersister $jsonPersister)
    {
        $this->lock               = $satisLockFactory->createLock('satis');
        $this->persisterInterface = $jsonPersister;
    }

    /**
     * Find repositories
     */
    public function getRepositories(): \ArrayIterator
    {
        return $this->getConfig()->getRepositories();
    }

    /**
     * Find one repository
     */
    public function findOneRepository(string $id): ?RepositoryInterface
    {
        return $this->getRepositories()[$id] ?? null;
    }

    public function findByUrl(string $url): ?RepositoryInterface
    {
        $repositories = $this->getRepositories();

        foreach ($repositories as $repository) {
            if (\mb_rtrim($repository->getUrl(), '/') === \mb_rtrim($url, '/')) {
                return $repository;
            }
        }

        $urlHost = \parse_url($url, \PHP_URL_HOST);
        if ($urlHost) {
            foreach ($repositories as $repository) {
                $repoHost = \parse_url($repository->getUrl(), \PHP_URL_HOST);
                if ($repoHost === $urlHost) {
                    return $repository;
                }
            }
        }

        foreach ($repositories as $repository) {
            if (\str_contains($repository->getUrl(), $url)) {
                return $repository;
            }
        }

        return null;
    }

    /**
     * Add a new repository
     */
    public function add(RepositoryInterface $repository): void
    {
        $lock = $this->acquireLock();
        try {
            $this
                ->doAdd($repository)
                ->flush();
        } finally {
            $lock->release();
        }
    }

    /**
     * Adds a array of repositories.
     *
     * @param RepositoryInterface[] $repositories
     */
    public function addAll(array $repositories): void
    {
        $lock = $this->acquireLock();
        try {
            foreach ($repositories as $repository) {
                $this->doAdd($repository);
            }
            $this->flush();
        } finally {
            $lock->release();
        }
    }

    /**
     * Update an existing repository
     *
     * @throws \RuntimeException
     */
    public function update(RepositoryInterface $repository, RepositoryInterface $updated): RepositoryInterface
    {
        $repos = $this->getRepositories();
        if (!$repos->offsetExists($repository->getId())) {
            throw new \RuntimeException('Unknown repository');
        }

        $lock = $this->acquireLock();
        try {
            $repos->offsetUnset($repository->getId());
            $repos->offsetSet($updated->getId(), $updated);
            $this->flush();
        } finally {
            $lock->release();
        }

        return $updated;
    }

    /**
     * Delete a repository
     */
    public function delete(RepositoryInterface $repository): void
    {
        $lock = $this->acquireLock();
        try {
            $this
                ->getConfig()
                ->getRepositories()
                ->offsetUnset($repository->getId());
            $this->flush();
        } finally {
            $lock->release();
        }
    }

    /**
     * Persist current configuration
     */
    public function flush(): void
    {
        $this->persisterInterface->flush($this->getConfig());
    }

    /**
     * Adds a single Repository without flush
     *
     * @return $this
     */
    private function doAdd(RepositoryInterface $repository): self
    {
        $this
            ->getConfig()
            ->getRepositories()
            ->offsetSet($repository->getId(), $repository);

        return $this;
    }

    public function getConfig(): Configuration
    {
        if ($this->configuration) {
            return $this->configuration;
        }

        try {
            $this->configuration = $this->persisterInterface->load();
        } catch (MissingConfigException $e) {
            // use default config if file is missing or empty
            $this->configuration = new Configuration();
        }

        return $this->configuration;
    }

    public function acquireLock(): LockInterface
    {
        if (!$this->lock->acquire()) {
            throw new IOException('Cannot acquire lock for satis configuration file');
        }

        return $this->lock;
    }
}
