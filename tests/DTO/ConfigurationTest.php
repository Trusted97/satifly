<?php

namespace App\Tests\DTO;

use App\DTO\Abandoned;
use App\DTO\Archive;
use App\DTO\Configuration;
use App\DTO\PackageConstraint;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = new Configuration();

        $this->assertSame('localhost/repository', $config->getName());
        $this->assertSame('', $config->getDescription());
        $this->assertSame('http://localhost', $config->getHomepage());
        $this->assertSame(Configuration::DEFAULT_OUTPUT_DIR, $config->getOutputDir());
        $this->assertTrue($config->isOutputHtml());
        $this->assertInstanceOf(\ArrayIterator::class, $config->getRepositories());
        $this->assertSame([], $config->getRequire());
        $this->assertFalse($config->isRequireAll());
        $this->assertFalse($config->isRequireDependencies());
        $this->assertFalse($config->isRequireDevDependencies());
        $this->assertTrue($config->isRequireDependencyFilter());
        $this->assertNull($config->getIncludeFilename());
        $this->assertInstanceOf(Archive::class, $config->getArchive());
        $this->assertSame('dev', $config->getMinimumStability());
        $this->assertSame([], $config->getMinimumStabilityPerPackage());
        $this->assertFalse($config->isProviders());
        $this->assertNull($config->getTwigTemplate());
        $this->assertSame([], $config->getAbandoned());
        $this->assertSame([], $config->getBlacklist());
        $this->assertNull($config->getConfig());
        $this->assertNull($config->getNotifyBatch());
        $this->assertTrue($config->isPrettyPrint());
        $this->assertNull($config->getStripHosts());
        $this->assertNull($config->getProvidersHistorySize());
        $this->assertNull($config->getComment());
    }

    public function testSettersAndGetters(): void
    {
        $config = new Configuration();

        $config->setName('vendor/package');
        $this->assertSame('vendor/package', $config->getName());

        $config->setDescription('Test description');
        $this->assertSame('Test description', $config->getDescription());

        $config->setHomepage('https://example.com');
        $this->assertSame('https://example.com', $config->getHomepage());

        $config->setOutputDir('dist');
        $this->assertSame('dist', $config->getOutputDir());

        $config->setOutputHtml(false);
        $this->assertFalse($config->isOutputHtml());

        $repo = [$this->createMock(\App\DTO\RepositoryInterface::class)];
        $config->setRepositories($repo);
        $this->assertInstanceOf(\ArrayIterator::class, $config->getRepositories());
        $this->assertSame($repo, \iterator_to_array($config->getRepositories()));

        $packageConstraint = [new PackageConstraint('foo', '^1.0')];
        $config->setRequire($packageConstraint);
        $this->assertSame($packageConstraint, $config->getRequire());

        $config->setRequireAll(true);
        $this->assertTrue($config->isRequireAll());

        $config->setRequireDependencies(true);
        $this->assertTrue($config->isRequireDependencies());

        $config->setRequireDevDependencies(true);
        $this->assertTrue($config->isRequireDevDependencies());

        $this->assertTrue($config->isRequireDependencyFilter());

        $archive = new Archive();
        $config->setArchive($archive);
        $this->assertSame($archive, $config->getArchive());

        $config->setMinimumStability('stable');
        $this->assertSame('stable', $config->getMinimumStability());

        $config->addMinimumStabilityPerPackage('foo/bar', 'dev');
        $this->assertCount(1, $config->getMinimumStabilityPerPackage());
        $this->assertSame('foo/bar', $config->getMinimumStabilityPerPackage()[0]->getPackage());

        $config->setProviders(true);
        $this->assertTrue($config->isProviders());

        $config->setTwigTemplate('template.twig');
        $this->assertSame('template.twig', $config->getTwigTemplate());

        $abandoned = [new Abandoned('old/package', 'new/package')];
        $config->setAbandoned($abandoned);
        $this->assertSame($abandoned, $config->getAbandoned());

        $config->setBlacklist(['bad/package']);
        $this->assertSame(['bad/package'], $config->getBlacklist());

        $config->setConfig(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $config->getConfig());

        $config->setConfig('{"x":1}');
        $this->assertSame(['x' => 1], $config->getConfig());

        $config->setConfig(null);
        $this->assertNull($config->getConfig());

        $config->setNotifyBatch('https://notify.example.com');
        $this->assertSame('https://notify.example.com', $config->getNotifyBatch());

        $this->assertTrue($config->isPrettyPrint());

        $config->setStripHosts(['example.com']);
        $this->assertSame(['example.com'], $config->getStripHosts());

        $config->setProvidersHistorySize(10);
        $this->assertSame(10, $config->getProvidersHistorySize());

        $config->setComment('Some comment');
        $this->assertSame('Some comment', $config->getComment());
    }
}
