<?php

namespace App\Tests\Traits;

use Symfony\Component\Filesystem\Filesystem;

trait TempFilesystemTrait
{
    protected string $tempRoot;
    private array $envBackup = [];

    protected function tempSetup(string $prefix = 'satifly-test'): void
    {
        $this->tempRoot = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . $prefix . '-' . \bin2hex(\random_bytes(6));

        (new Filesystem())->mkdir($this->tempRoot);
    }

    protected function tempTearDown(): void
    {
        $this->clearSatisTestEnv();

        if (isset($this->tempRoot) && \is_dir($this->tempRoot)) {
            (new Filesystem())->remove($this->tempRoot);
        }
    }

    protected function tempPath(string $path = ''): string
    {
        if ('' === $path) {
            return $this->tempRoot;
        }

        return $this->tempRoot . \DIRECTORY_SEPARATOR . $path;
    }

    protected function writeTempFile(string $relativePath, string $content): string
    {
        $path = $this->tempPath($relativePath);

        (new Filesystem())->mkdir(\dirname($path));
        \file_put_contents($path, $content);

        return $path;
    }

    protected function configureSatisTestEnv(): void
    {
        $this->backupEnv('SATIS_CONFIG');
        $this->backupEnv('SATIS_LOG');

        $this->setEnv('SATIS_CONFIG', $this->tempPath('satis.json'));
        $this->setEnv('SATIS_LOG', $this->tempPath('satis'));

        (new Filesystem())->mkdir($this->tempPath('satis'));
    }

    protected function clearSatisTestEnv(): void
    {
        $this->restoreEnv('SATIS_CONFIG');
        $this->restoreEnv('SATIS_LOG');
    }

    private function setEnv(string $name, string $value): void
    {
        \putenv($name . '=' . $value);
        $_SERVER[$name] = $value;
        $_ENV[$name] = $value;
    }

    private function backupEnv(string $name): void
    {
        if (!\array_key_exists($name, $this->envBackup)) {
            $this->envBackup[$name] = [
                'putenv' => \getenv($name),
                '_SERVER' => $_SERVER[$name] ?? null,
                '_ENV' => $_ENV[$name] ?? null,
            ];
        }
    }

    private function restoreEnv(string $name): void
    {
        $backup = $this->envBackup[$name] ?? null;

        if (null === $backup) {
            \putenv($name);
            unset($_SERVER[$name], $_ENV[$name]);

            return;
        }

        if (null === $backup['putenv']) {
            \putenv($name);
        } else {
            \putenv($name . '=' . $backup['putenv']);
        }

        if (null === $backup['_SERVER']) {
            unset($_SERVER[$name]);
        } else {
            $_SERVER[$name] = $backup['_SERVER'];
        }

        if (null === $backup['_ENV']) {
            unset($_ENV[$name]);
        } else {
            $_ENV[$name] = $backup['_ENV'];
        }
    }
}
