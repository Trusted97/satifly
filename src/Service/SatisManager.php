<?php

namespace App\Service;

use App\Event\BuildEvent;
use App\Process\ProcessFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Process\Exception\RuntimeException;

#[AsEventListener(event: BuildEvent::class, method: 'onBuild', priority: 100)]
class SatisManager
{
    protected string $satisFilename;

    protected ProcessFactory $processFactory;

    protected LoggerInterface $logger;

    protected int $timeout = 600;

    protected LockInterface $lock;

    protected RepositoryManager $manager;

    public function __construct(
        string $satisFilename,
        LockFactory $buildLockFactory,
        ProcessFactory $processFactory,
        RepositoryManager $manager,
        LoggerInterface $logger,
    ) {
        $this->satisFilename  = $satisFilename;
        $this->lock           = $buildLockFactory->createLock('build');
        $this->processFactory = $processFactory;
        $this->manager        = $manager;
        $this->logger         = $logger;
    }

    public function run(): \Generator
    {
        $this->lock->acquire(true);
        try {
            $process = $this->processFactory->create($this->getCommandLine(), $this->timeout);
            $process->start();

            yield $process->getCommandLine();

            foreach ($process as $line) {
                $line = $this->trimLine($line);
                if (empty($line)) {
                    continue;
                }
                yield $line;
            }

            yield $process->getExitCodeText();
        } finally {
            $this->lock->release();
        }
    }

    /**
     * Handle a BuildEvent by executing the Satis build for the event's repository.
     *
     * Acquires the build lock, runs the generated Satis process, releases the lock,
     * and updates the event's status with the process exit code (`1` if an internal
     * RuntimeException occurs).
     *
     * @param BuildEvent $event The build event; its repository (if any) is used to
     *                          determine the build target and its status will be
     *                          updated with the process exit code.
     */
    public function onBuild(BuildEvent $event): void
    {
        $repository = $event->getRepository();
        $command    = $this->getCommandLine($repository?->getName());
        $process    = $this->processFactory->create($command, $this->timeout);

        try {
            $this->lock->acquire(true);
            $status = $process->run();
        } catch (RuntimeException $exception) {
            $status = 1;
        } finally {
            $this->lock->release();
        }

        $event->setStatus($status);
    }

    /**
     * Build the command argument array for executing a Satis build.
     *
     * Constructs a command array based on the configured satis file and output directory,
     * optionally scoping the build to a single repository and adding extra options or arguments.
     *
     * @param string|null $repositoryName Name of a single repository to target, or `null` to include all repositories.
     * @param array $options Associative or list-style Satis options to include (added via the builder's options API).
     * @param array $extraArgs Additional positional arguments to append to the command.
     * @return array The constructed command as an array of command and arguments suitable for Process execution.
     * @throws \JsonException If encoding the built command to JSON for logging fails.
     */
    protected function getCommandLine(?string $repositoryName = null, array $options = [], array $extraArgs = []): array
    {
        $configuration = $this->manager->getConfig();
        $outputDir     = $configuration->getOutputDir();

        $satisCommandBuilder = SatisCommandBuilder::from(configFile: $this->satisFilename, outputDir: $outputDir);

        if (!empty($options)) {
            $satisCommandBuilder->addOptions($options);
        }

        if (!empty($extraArgs)) {
            $satisCommandBuilder->addArgs($extraArgs);
        }

        if (null !== $repositoryName) {
            $satisCommandBuilder->withRepository($repositoryName);
        }

        $this->logger->info(\json_encode($satisCommandBuilder->build(), \JSON_THROW_ON_ERROR));

        return $satisCommandBuilder->build();
    }

    protected function trimLine(string $line): string
    {
        return \mb_trim($line, " \t\n\r\0\x0B\x08");
    }
}