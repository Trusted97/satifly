<?php

namespace App\Persister;

use App\DTO\Abandoned;
use App\DTO\Archive;
use App\DTO\Configuration;
use App\DTO\PackageConstraint;
use App\DTO\PackageStability;
use App\DTO\RepositoryInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * JSON-based persister for Satis configuration.
 *
 * Wraps a lower-level persister and handles serialization and deserialization
 * of Configuration objects to/from JSON.
 */
class JsonPersister implements PersisterInterface
{
    use SerializerAwareTrait;

    /**
     * @var PersisterInterface Persister that handles raw file I/O
     */
    private PersisterInterface $persister;

    /**
     * Fully qualified class name of the Configuration DTO.
     */
    private string $satisClass;

    /**
     * Constructor.
     *
     * @param PersisterInterface  $persister  Underlying file persister
     * @param SerializerInterface $serializer Symfony serializer
     * @param string              $satisClass Fully qualified DTO class name
     */
    public function __construct(PersisterInterface $persister, SerializerInterface $serializer, string $satisClass)
    {
        $this->setSerializer($serializer);
        $this->persister  = $persister;
        $this->satisClass = $satisClass;
    }

    /**
     * Load and deserialize configuration from JSON.
     *
     * @throws \RuntimeException|ExceptionInterface If the JSON is empty
     */
    public function load(): Configuration
    {
        $jsonString = $this->persister->load();

        if ('' === \mb_trim($jsonString)) {
            throw new \RuntimeException('Satis file is empty.');
        }

        return $this->serializer->deserialize($jsonString, $this->satisClass, 'json');
    }

    /**
     * Serialize and persist the Configuration object to JSON.
     *
     * @param object|string $content Configuration object
     *
     * @throws ExceptionInterface On serialization error
     */
    public function flush(object|string $content): void
    {
        $jsonString = $this->serializer->serialize($content, 'json', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractNormalizer::CALLBACKS              => [
                'repositories'               => [$this, 'normalizeRepositories'],
                'require'                    => [$this, 'normalizeRequire'],
                'blacklist'                  => [$this, 'normalizeRequire'],
                'abandoned'                  => [$this, 'normalizeAbandoned'],
                'minimumStabilityPerPackage' => [$this, 'normalizePackageStability'],
                'archive'                    => [$this, 'normalizeArchive'],
            ],
            JsonEncode::OPTIONS => \JSON_PRETTY_PRINT,
        ]);

        $this->persister->flush($jsonString);
    }

    /**
     * Normalize repositories into an array suitable for JSON serialization.
     *
     * @return array<string, RepositoryInterface>
     */
    public function normalizeRepositories(?\ArrayIterator $repositories): array
    {
        if ($repositories instanceof \ArrayIterator) {
            return $repositories->getArrayCopy();
        }

        return [];
    }

    /**
     * Normalize array of PackageConstraint objects into associative array.
     *
     * @param PackageConstraint[]|null $constraints
     *
     * @return string[]|null
     */
    public function normalizeRequire(?array $constraints): ?array
    {
        if (empty($constraints)) {
            return null;
        }

        $require = [];
        foreach ($constraints as $constraint) {
            $require[$constraint->getPackage()] = $constraint->getConstraint();
        }

        return $require;
    }

    /**
     * Normalize abandoned packages into associative array.
     *
     * @param Abandoned[]|null $abandoned
     *
     * @return array<string, bool|string>|null
     */
    public function normalizeAbandoned(?array $abandoned): ?array
    {
        if (empty($abandoned)) {
            return null;
        }

        $list = [];
        foreach ($abandoned as $package) {
            $replacement                  = $package->getReplacement() ?: true;
            $list[$package->getPackage()] = $replacement;
        }

        return $list;
    }

    /**
     * Normalize package stability array into associative array.
     *
     * @param PackageStability[] $list
     *
     * @return array<string, string>|null
     */
    public function normalizePackageStability(array $list): ?array
    {
        if (empty($list)) {
            return null;
        }

        $data = [];
        foreach ($list as $item) {
            $data[$item->getPackage()] = $item->getStability();
        }

        return $data;
    }

    /**
     * Normalize Archive object into array for JSON serialization.
     *
     * @param Archive|null $archive
     *
     * @return array<string, mixed>
     */
    public function normalizeArchive($archive): array
    {
        if ($archive instanceof Archive) {
            return [
                'directory'          => $archive->getDirectory() ?: '',
                'format'             => $archive->getFormat() ?: 'zip',
                'skip-dev'           => $archive->isSkipDev(),
                'whitelist'          => $archive->getWhitelist(),
                'blacklist'          => $archive->getBlacklist(),
                'checksum'           => $archive->isChecksum(),
                'ignore-filters'     => $archive->isIgnoreFilters(),
                'override-dist-type' => $archive->isOverrideDistType(),
                'rearchive'          => $archive->isRearchive(),
            ];
        }

        return [
            'directory'          => '',
            'format'             => 'zip',
            'skip-dev'           => true,
            'whitelist'          => [],
            'blacklist'          => [],
            'checksum'           => true,
            'ignore-filters'     => false,
            'override-dist-type' => false,
            'rearchive'          => true,
        ];
    }
}
