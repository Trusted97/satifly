<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

class Configuration
{
    public const string DEFAULT_OUTPUT_DIR = 'public';

    private string $name        = 'localhost/repository';
    private string $description = '';
    private string $homepage    = 'http://localhost';

    #[SerializedName('output-dir')]
    private string $outputDir = self::DEFAULT_OUTPUT_DIR;

    #[SerializedName('output-html')]
    private bool $outputHtml = true;

    /**
     * @var \ArrayObject<string, RepositoryInterface>|RepositoryInterface[]
     */
    private \ArrayIterator|array $repositories;

    /**
     * @var PackageConstraint[]
     */
    #[SerializedName('require')]
    private array $require = [];

    #[SerializedName('require-all')]
    private bool $requireAll = false;

    #[SerializedName('require-dependencies')]
    private bool $requireDependencies = false;

    #[SerializedName('require-dev-dependencies')]
    private bool $requireDevDependencies = false;

    #[SerializedName('require-dependency-filter')]
    private bool $requireDependencyFilter = true;

    /**
     * @var string[]|null
     */
    #[SerializedName('strip-hosts')]
    private ?array $stripHosts = null;

    #[SerializedName('include-filename')]
    private ?string $includeFilename = null;

    #[SerializedName('archive')]
    private ?Archive $archive = null;

    #[SerializedName('minimum-stability')]
    private ?string $minimumStability = 'dev';

    /**
     * @var PackageStability[]
     */
    #[SerializedName('minimum-stability-per-package')]
    private array $minimumStabilityPerPackage = [];

    private bool $providers = false;

    #[SerializedName('providers-history-size')]
    private ?int $providersHistorySize = null;

    #[SerializedName('twig-template')]
    private ?string $twigTemplate = null;

    /**
     * @var Abandoned[]
     */
    private array $abandoned = [];

    /**
     * @var PackageConstraint[]
     */
    private array $blacklist = [];

    /**
     * @var mixed[]|null
     */
    private ?array $config = null;

    #[SerializedName('notify-batch')]
    private ?string $notifyBatch = null;

    #[SerializedName('_comment')]
    private ?string $comment = null;

    #[SerializedName('pretty-print')]
    private bool $prettyPrint = true;

    public function __construct()
    {
        $this->repositories = new \ArrayIterator();
        $this->archive      = new Archive();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description = null): void
    {
        $this->description = $description;
    }

    public function getHomepage(): string
    {
        return $this->homepage;
    }

    public function setHomepage(string $homepage): void
    {
        $this->homepage = $homepage;
    }

    public function getOutputDir(): ?string
    {
        return $this->outputDir;
    }

    public function setOutputDir(string $outputDir): void
    {
        $this->outputDir = $outputDir;
    }

    public function isOutputHtml(): bool
    {
        return $this->outputHtml;
    }

    public function setOutputHtml(bool $outputHtml): void
    {
        $this->outputHtml = $outputHtml;
    }

    public function isRequireAll(): bool
    {
        return $this->requireAll;
    }

    public function setRequireAll(bool $requireAll): void
    {
        $this->requireAll = $requireAll;
    }

    public function isRequireDependencies(): bool
    {
        return $this->requireDependencies;
    }

    public function setRequireDependencies(bool $requireDependencies): void
    {
        $this->requireDependencies = $requireDependencies;
    }

    public function isRequireDevDependencies(): bool
    {
        return $this->requireDevDependencies;
    }

    public function setRequireDevDependencies(bool $requireDevDependencies): void
    {
        $this->requireDevDependencies = $requireDevDependencies;
    }

    public function isRequireDependencyFilter(): bool
    {
        return $this->requireDependencyFilter;
    }

    public function getIncludeFilename(): ?string
    {
        return $this->includeFilename;
    }

    /**
     * @return \ArrayIterator&iterable<RepositoryInterface>
     */
    public function getRepositories(): \ArrayIterator
    {
        return $this->repositories;
    }

    /**
     * @param array|\ArrayIterator|RepositoryInterface[] $repositories
     */
    public function setRepositories(array|\ArrayIterator $repositories): self
    {
        if (\is_array($repositories)) {
            $repositories = new \ArrayIterator($repositories);
        }
        $this->repositories = $repositories;

        return $this;
    }

    /**
     * @return PackageConstraint[]|null
     */
    public function getRequire(): ?array
    {
        return $this->require;
    }

    /**
     * @param PackageConstraint[] $require
     */
    public function setRequire(array $require): self
    {
        Assert::allIsInstanceOf($require, PackageConstraint::class);
        $this->require = $require;

        return $this;
    }

    // ✅ Archive integration
    public function getArchive(): ?Archive
    {
        return $this->archive;
    }

    public function setArchive(?Archive $archive = null): void
    {
        $this->archive = $archive;
    }

    public function getMinimumStability(): ?string
    {
        return $this->minimumStability;
    }

    public function setMinimumStability(?string $minimumStability): void
    {
        $this->minimumStability = $minimumStability;
    }

    /**
     * @return PackageStability[]
     */
    public function getMinimumStabilityPerPackage(): array
    {
        return $this->minimumStabilityPerPackage;
    }

    /**
     * @param PackageStability[] $minimumStabilityPerPackage
     */
    public function setMinimumStabilityPerPackage(array $minimumStabilityPerPackage): void
    {
        $this->minimumStabilityPerPackage = $minimumStabilityPerPackage;
    }

    public function addMinimumStabilityPerPackage(string $package, string $stability): void
    {
        $this->minimumStabilityPerPackage[] = new PackageStability($package, $stability);
    }

    public function isProviders(): bool
    {
        return $this->providers;
    }

    public function setProviders(bool $providers): void
    {
        $this->providers = $providers;
    }

    public function getTwigTemplate(): ?string
    {
        return $this->twigTemplate;
    }

    public function setTwigTemplate(?string $twigTemplate = null): void
    {
        $this->twigTemplate = $twigTemplate;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig($config): void
    {
        if (empty($config)) {
            $this->config = null;
        } elseif (\is_string($config)) {
            $this->config = \json_decode($config, true);
        } else {
            $this->config = $config;
        }
    }

    public function setNotifyBatch(?string $notifyBatch = null): void
    {
        $this->notifyBatch = $notifyBatch;
    }

    public function getNotifyBatch(): ?string
    {
        return $this->notifyBatch;
    }

    public function isPrettyPrint(): bool
    {
        return $this->prettyPrint;
    }

    /**
     * @return string[]|null
     */
    public function getStripHosts(): ?array
    {
        return $this->stripHosts;
    }

    /**
     * @param string[] $stripHosts
     */
    public function setStripHosts(?array $stripHosts): void
    {
        $this->stripHosts = $stripHosts;
    }

    public function getProvidersHistorySize(): ?int
    {
        return $this->providersHistorySize;
    }

    public function setProvidersHistorySize(?int $providersHistorySize): void
    {
        $this->providersHistorySize = $providersHistorySize;
    }

    /**
     * @return Abandoned[]|null
     */
    public function getAbandoned(): ?array
    {
        return $this->abandoned;
    }

    /**
     * @param Abandoned[]|null $abandoned
     */
    public function setAbandoned(?array $abandoned): void
    {
        $this->abandoned = $abandoned;
    }

    public function getBlacklist(): ?array
    {
        return $this->blacklist;
    }

    public function setBlacklist(?array $blacklist): void
    {
        $this->blacklist = $blacklist;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }
}
