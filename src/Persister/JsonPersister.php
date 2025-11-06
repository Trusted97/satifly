<?php

namespace App\Persister;

use App\DTO\Abandoned;
use App\DTO\Archive;
use App\DTO\Configuration;
use App\DTO\PackageConstraint;
use App\DTO\PackageStability;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Symfony\Component\Serializer\SerializerInterface;

class JsonPersister implements PersisterInterface
{
    use SerializerAwareTrait;

    private PersisterInterface $persister;

    private string $satisClass;

    public function __construct(PersisterInterface $persister, SerializerInterface $serializer, string $satisClass)
    {
        $this->setSerializer($serializer);
        $this->persister  = $persister;
        $this->satisClass = $satisClass;
    }

    public function load(): Configuration
    {
        $jsonString = $this->persister->load();
        if ('' === \mb_trim($jsonString)) {
            throw new \RuntimeException('Satis file is empty.');
        }

        return $this->serializer->deserialize($jsonString, $this->satisClass, 'json');
    }

    /**
     * @throws ExceptionInterface
     */
    public function flush(object $content): void
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

    public function normalizeRepositories($repositories): array
    {
        if ($repositories instanceof \ArrayIterator) {
            return \iterator_to_array($repositories->getArrayCopy());
        }

        return [];
    }

    /**
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
            $replacement = $package->getReplacement();
            if (empty($replacement)) {
                $replacement = true;
            }
            $list[$package->getPackage()] = $replacement;
        }

        return $list;
    }

    /**
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
            'checksum'           => true,
            'ignore-filters'     => false,
            'override-dist-type' => false,
            'rearchive'          => true,
        ];
    }
}
