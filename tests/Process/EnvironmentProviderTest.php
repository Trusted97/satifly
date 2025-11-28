<?php

namespace App\Tests\Process;

use App\Process\EnvironmentProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EnvironmentProviderTest extends KernelTestCase
{
    protected function tearDown(): void
    {
        \putenv('TEST_KEY');
        unset($_SERVER['TEST_KEY'], $_ENV['TEST_ENV_KEY']);
    }

    public function testGetEnvSetsComposerDefaults(): void
    {
        $parameterBag = static::getContainer()->get(ParameterBagInterface::class);
        $provider     = new EnvironmentProvider($parameterBag->get('composer.home'));

        $env = $provider->getEnv();

        $this->assertSame('/app/var/composer', $env['COMPOSER_HOME']);
        $this->assertSame('1', $env['COMPOSER_NO_INTERACTION']);
    }

    public function testGetEnvIncludesServerVariables(): void
    {
        $_SERVER['TEST_KEY'] = 'value';
        \putenv('TEST_KEY=value');

        $parameterBag = static::getContainer()->get(ParameterBagInterface::class);
        $provider     = new EnvironmentProvider($parameterBag->get('composer.home'));

        $env = $provider->getEnv();

        $this->assertArrayHasKey('TEST_KEY', $env);
        $this->assertSame('value', $env['TEST_KEY']);
    }

    public function testGetEnvIncludesEnvVariables(): void
    {
        $_ENV['TEST_ENV_KEY'] = 'env_value';

        $parameterBag = static::getContainer()->get(ParameterBagInterface::class);
        $provider     = new EnvironmentProvider($parameterBag->get('composer.home'));

        $env = $provider->getEnv();

        $this->assertArrayHasKey('TEST_ENV_KEY', $env);
        $this->assertSame('env_value', $env['TEST_ENV_KEY']);
    }

    public function testComposerHomeNotOverwrittenIfProvidedInEnv(): void
    {
        \putenv('COMPOSER_HOME=/app/var/composer');
        $_SERVER['COMPOSER_HOME'] = '/app/var/composer';

        $parameterBag = static::getContainer()->get(ParameterBagInterface::class);
        $provider     = new EnvironmentProvider($parameterBag->get('composer.home'));

        $env = $provider->getEnv();

        $this->assertSame('/app/var/composer', $env['COMPOSER_HOME']);
    }
}
