<?php

namespace App\Persister;

use App\Exception\MissingConfigException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Persists configuration data to a file.
 *
 * Handles reading, writing, backup creation, and permission checks.
 */
class FilePersister implements PersisterInterface
{
    private Filesystem $filesystem;

    /**
     * Path to the Satis configuration file.
     */
    private string $filename;

    /**
     * Directory path where backups/logs are stored.
     */
    private string $logPath;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem Filesystem service
     * @param string     $filename   Path to configuration file
     * @param string     $logPath    Path to store backups
     */
    public function __construct(Filesystem $filesystem, string $filename, string $logPath)
    {
        $this->filesystem = $filesystem;
        $this->filename   = $filename;
        $this->logPath    = $logPath;
    }

    /**
     * Load content from the configuration file.
     *
     * @throws MissingConfigException When config file is missing or empty
     */
    public function load(): string
    {
        if (!$this->filesystem->exists($this->filename)) {
            throw new MissingConfigException('Satis file is missing');
        }

        try {
            $content = \mb_trim(\file_get_contents($this->filename));
        } catch (\Exception $exception) {
            throw new \RuntimeException(\sprintf('Unable to load the data from "%s"', $this->filename), 0, $exception);
        }

        if (empty($content)) {
            throw new MissingConfigException('Satis file is empty');
        }

        return $content;
    }

    /**
     * Persist content to the configuration file.
     *
     * Creates a backup before writing and checks permissions.
     *
     * @throws \RuntimeException On write failure
     */
    public function flush(object|string $content): void
    {
        try {
            $this->checkPermissions();
            $this->createBackup();

            if (false === @\file_put_contents($this->filename, $content)) {
                throw new IOException(\sprintf('Failed to write file "%s".', $this->filename), 0, null, $this->filename);
            }
        } catch (\Exception $exception) {
            throw new \RuntimeException(\sprintf('Unable to persist the data to "%s"', $this->filename), 0, $exception);
        }
    }

    /**
     * Create a timestamped backup of the current configuration file.
     */
    public function createBackup(): void
    {
        if (!\file_exists($this->filename)) {
            return;
        }

        if (!$this->filesystem->exists($this->logPath) || !\is_writable($this->logPath)) {
            return;
        }

        $path = \mb_rtrim($this->logPath, '/');
        $name = \sprintf('%s.json', \date('Y-m-d_his'));
        $this->filesystem->copy($this->filename, $path . '/' . $name);
    }

    /**
     * Check write permissions for the configuration file and its directory.
     *
     * @throws IOException When file or directory is not writable
     */
    protected function checkPermissions(): void
    {
        if (\file_exists($this->filename)) {
            if (!\is_writable($this->filename)) {
                throw new IOException(\sprintf('File "%s" is not writable.', $this->filename));
            }
        } elseif (!\is_writable(\dirname($this->filename))) {
            throw new IOException(\sprintf('Path "%s" is not writable.', \dirname($this->filename)));
        }
    }
}
