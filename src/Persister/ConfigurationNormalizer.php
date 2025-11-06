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

class ConfigurationNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private SerializerInterface $serializer;

    public function normalize($data, ?string $format = null, array $context = []): float|array|\ArrayObject|bool|int|string|null
    {
        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): false
    {
        return false;
    }

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

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        switch ($type) {
            case PackageConstraint::class . '[]':
            case RepositoryInterface::class . '[]':
            case PackageStability::class . '[]':
            case Abandoned::class . '[]':
            case Archive::class:
                return true;
            default:
        }

        return false;
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * @return PackageConstraint[]
     */
    private function denormalizeRequire($data): array
    {
        $require = [];
        foreach ($data as $package => $constraint) {
            $require[] = new PackageConstraint($package, $constraint);
        }

        return $require;
    }

    private function denormalizeRepositories($data): \ArrayIterator
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

    private function denormalizePackageStability($data): array
    {
        $list = [];
        foreach ($data as $package => $stability) {
            $list[] = new PackageStability($package, $stability);
        }

        return $list;
    }

    private function denormalizeAbandoned($data): array
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

    private function denormalizeArchive($data): Archive
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
