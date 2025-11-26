<?php

namespace App\Service;

final class SatisCommandBuilder
{
    private ?string $repositoryName = null;

    /** @var string[] */
    private array $options = [
        '--skip-errors',
        '--no-ansi',
        '--verbose',
    ];

    /** @var string[] */
    private array $extraArgs = [];

    private function __construct(
        public string $configFile,
        public string $outputDir,
    ) {
    }

    public static function from(
        string $configFile,
        string $outputDir,
    ): self {
        return new self(configFile: $configFile, outputDir: $outputDir);
    }

    public function withRepository(string $repositoryName): self
    {
        $this->repositoryName = $repositoryName;

        return $this;
    }

    protected function addOption(string $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    public function addOptions(array $options): self
    {
        $this->options = \array_merge($this->options, $options);

        return $this;
    }

    public function addArgs(array $args): self
    {
        $this->extraArgs = \array_merge($this->extraArgs, $args);

        return $this;
    }

    public function build(): array
    {
        $command = [
            'vendor/bin/satis',
            'build',
            $this->configFile,
            $this->outputDir,
        ];

        if (null !== $this->repositoryName) {
            $this->addOption(\sprintf('%s', $this->repositoryName));
        }

        $options = \array_values(\array_unique($this->options));

        return \array_merge($command, $options, $this->extraArgs);
    }
}
