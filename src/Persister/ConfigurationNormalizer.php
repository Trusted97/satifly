<?php

namespace App\Persister;

use App\DTO\Abandoned;
use App\DTO\Archive;
use App\DTO\PackageConstraint;
use App\DTO\PackageStability;
use App\DTO\Repository;
use App\DTO\RepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalizer and denormalizer for Satis configuration DTOs.
 *
 * Handles conversion between arrays and DTO objects for:
 * - PackageConstraint
 * - Repository
 * - PackageStability
 * - Abandoned
 * - Archive
 */
class ConfigurationNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private SerializerInterface $serializer;

    /**
     * Normalize data to array or scalar.
     */
    public function normalize($data, ?string $format = null, array $context = []): float|array|\ArrayObject|bool|int|string|null
    {
        return $data;
    }

    /**
     * Check if normalization is supported.
     */
    public function supportsNormalization($data, ?string $format = null, array $context = []): false
    {
        return false;
    }

    /**
     * Denormalize data into DTO objects.
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if ($type === PackageConstraint::class . '[]') {
            return $this->denormalizeRequire($data);
        }

        if ($type === RepositoryInterface::class . '[]') {
            return $this->denormalizeRepositories($data);
        }

        if ($type === PackageStability::class . '[]') {
            return $this->denormalizePackageStability($data);
        }

        if ($type === Abandoned::class . '[]') {
            return $this->denormalizeAbandoned($data);
        }

        if (Archive::class === $type) {
            return $this->denormalizeArchive($data);
        }

        if ($this->serializer instanceof DenormalizerInterface) {
            return $this->serializer->denormalize($data, $type, $format, $context);
        }

        return $data;
    }

    /**
     * Check if denormalization is supported.
     */
    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return match ($type) {
            PackageConstraint::class . '[]',
            RepositoryInterface::class . '[]',
            PackageStability::class . '[]',
            Abandoned::class . '[]',
            Archive::class => true,
            default        => false,
        };
    }

    /**
     * Inject serializer for nested denormalization.
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * Denormalize require section into PackageConstraint objects.
     *
     * @return PackageConstraint[]
     */
    private function denormalizeRequire(array $data): array
    {
        $require = [];
        foreach ($data as $package => $constraint) {
            $require[] = new PackageConstraint($package, $constraint);
        }

        return $require;
    }

    /**
     * Denormalize repositories array into Repository objects.
     *
     * @return \ArrayIterator<int, RepositoryInterface>
     */
    private function denormalizeRepositories(array $data): \ArrayIterator
    {
        $list = [];
        foreach ($data as $item) {
            $repository = new Repository(url: $item['url'], type: $item['type'], name: $item['name']);
            if (!empty($item['installation-source'])) {
                $repository->setInstallationSource($item['installation-source']);
            }
            $list[$repository->getId()] = $repository;
        }

        return new \ArrayIterator($list);
    }

    /**
     * Denormalize package stability array into PackageStability objects.
     *
     * @return PackageStability[]
     */
    private function denormalizePackageStability(array $data): array
    {
        $list = [];
        foreach ($data as $package => $stability) {
            $list[] = new PackageStability($package, $stability);
        }

        return $list;
    }

    /**
     * Denormalize abandoned packages array into Abandoned objects.
     *
     * @return Abandoned[]
     */
    private function denormalizeAbandoned(array $data): array
    {
        $list = [];
        foreach ($data as $package => $replacement) {
            if (!\is_string($replacement)) {
                $replacement = null;
            }
            $list[] = new Abandoned($package, $replacement);
        }

        return $list;
    }

    /**
     * Denormalize archive array into Archive object.
     */
    private function denormalizeArchive(array $data): Archive
    {
        $archive = new Archive();
        $archive->setDirectory($data['directory'] ?? null);
        $archive->setFormat($data['format'] ?? null);
        $archive->setSkipDev((bool) ($data['skip-dev'] ?? false));
        $archive->setAbsoluteDirectory($data['absolute-directory'] ?? null);
        $archive->setPrefixUrl($data['prefix-url'] ?? null);
        $archive->setChecksum((bool) ($data['checksum'] ?? false));
        $archive->setIgnoreFilters((bool) ($data['ignore-filters'] ?? false));
        $archive->setOverrideDistType((bool) ($data['override-dist-type'] ?? false));
        $archive->setRearchive((bool) ($data['rearchive'] ?? false));
        $archive->setWhitelist($data['whitelist'] ?? []);
        $archive->setBlacklist($data['blacklist'] ?? []);

        return $archive;
    }

    /**
     * List of supported types for denormalization.
     *
     * @return array<string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            Abandoned::class . '[]'           => true,
            PackageConstraint::class . '[]'   => true,
            PackageStability::class . '[]'    => true,
            RepositoryInterface::class . '[]' => true,
            Archive::class                    => true,
        ];
    }
}
