<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

/**
 * Main configuration class for Satis repository setup.
 *
 * Represents the overall configuration of a repository including:
 *  - Repository metadata (name, description, homepage)
 *  - Output settings (directory, HTML output)
 *  - Package requirements and stability rules
 *  - Archive options
 *  - Providers, abandoned packages, and blacklists
 *
 * This class is used for both reading and writing Satis configuration.
 */
class Configuration
{
    /**
     * Default output directory for Satis-generated files.
     */
    public const string DEFAULT_OUTPUT_DIR = 'public';

    /** @var string Repository name */
    public string $name = 'localhost/repository';

    /** @var string Repository description */
    public string $description = '';

    /** @var string Repository homepage URL */
    public string $homepage = 'http://localhost';

    /** @var string Output directory for Satis files */
    #[SerializedName('output-dir')]
    public string $outputDir = self::DEFAULT_OUTPUT_DIR;

    /** @var bool Whether to generate HTML output */
    #[SerializedName('output-html')]
    public bool $outputHtml = true;

    /**
     * @var \ArrayIterator<int, RepositoryInterface> List of repositories
     */
    private \ArrayIterator $repositories;

    /** @var PackageConstraint[] List of required packages */
    #[SerializedName('require')]
    public array $require = [];

    /** @var bool Whether to require all packages */
    #[SerializedName('require-all')]
    public bool $requireAll = false;

    #[SerializedName('require-dependencies')]
    public bool $requireDependencies = false;

    #[SerializedName('require-dev-dependencies')]
    public bool $requireDevDependencies = false;

    #[SerializedName('require-dependency-filter')]
    public bool $requireDependencyFilter = true;

    /** @var string[]|null Hosts to strip from package URLs */
    #[SerializedName('strip-hosts')]
    private ?array $stripHosts = null;

    /** @var string|null Optional included configuration filename */
    #[SerializedName('include-filename')]
    public ?string $includeFilename = null;

    /** @var Archive|null Archive configuration */
    #[SerializedName('archive')]
    public ?Archive $archive = null;

    /** @var string|null Minimum stability level for packages */
    #[SerializedName('minimum-stability')]
    public ?string $minimumStability = 'dev';

    /** @var PackageStability[] Minimum stability per package */
    #[SerializedName('minimum-stability-per-package')]
    public array $minimumStabilityPerPackage = [];

    /** @var bool Whether to generate providers */
    public bool $providers = false;

    /** @var int|null Size of provider history */
    #[SerializedName('providers-history-size')]
    private ?int $providersHistorySize = null;

    /** @var string|null Twig template name */
    #[SerializedName('twig-template')]
    public ?string $twigTemplate = null;

    /** @var Abandoned[] List of abandoned packages */
    public array $abandoned = [];

    /** @var PackageConstraint[] List of blacklisted packages */
    public array $blacklist = [];

    /** @var array|null Raw configuration array */
    public ?array $config = null;

    /** @var string|null Batch notification email */
    #[SerializedName('notify-batch')]
    public ?string $notifyBatch = null;

    /** @var string|null Optional comment in configuration */
    #[SerializedName('_comment')]
    private ?string $comment = null;

    /** @var bool Whether to pretty-print JSON output */
    #[SerializedName('pretty-print')]
    public bool $prettyPrint = true;

    /**
     * Constructor initializes default values for repositories and archive.
     */
    public function __construct()
    {
        $this->repositories = new \ArrayIterator();
        $this->archive      = new Archive();
    }

    // -------------------
    // Getter and Setter Methods
    // -------------------

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
        $list = [];

        foreach ($repositories as $key => $repository) {
            if ($repository instanceof RepositoryInterface) {
                $list[$repository->getId()] = $repository;
                continue;
            }

            if (!\is_array($repository)) {
                continue;
            }

            $item = new Repository(
                url: (string) ($repository['url'] ?? ''),
                type: (string) ($repository['type'] ?? 'vcs'),
                name: (string) ($repository['name'] ?? '')
            );

            if (!empty($repository['installation-source'])) {
                $item->setInstallationSource((string) $repository['installation-source']);
            }

            $list[\is_string($key) ? $key : $item->getId()] = $item;
        }

        $this->repositories = new \ArrayIterator($list);

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
