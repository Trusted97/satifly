<?php

namespace App\Process;

class EnvironmentProvider
{
    public function __construct(
        private readonly string $composerHome,
    ) {
    }

    public function getEnv(): array
    {
        $env = [];

        foreach ($_SERVER as $key => $value) {
            if (\is_string($value) && false !== $envValue = \getenv($key)) {
                $env[$key] = $envValue;
            }
        }

        foreach ($_ENV as $key => $value) {
            if (\is_string($value)) {
                $env[$key] = $value;
            }
        }

        $env['COMPOSER_HOME'] ??= $this->composerHome;
        $env['COMPOSER_NO_INTERACTION'] = '1';

        return $env;
    }
}
