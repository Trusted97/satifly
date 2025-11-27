<?php

namespace App\Process;

use Symfony\Component\Process\Process;

final readonly class ProcessFactory
{
    public function __construct(
        private string $rootPath,
        private EnvironmentProvider $envProvider
    ) {}

    public function create(array $command, ?int $timeout = null): Process
    {
        if (empty($command)) {
            throw new \InvalidArgumentException('Command array cannot be empty.');
        }

        $command[0] = $this->rootPath . '/' . $command[0];

        return new Process(
            command: $command,
            cwd: $this->rootPath,
            env: $this->envProvider->getEnv(),
            input: null,
            timeout: $timeout
        );
    }
}
