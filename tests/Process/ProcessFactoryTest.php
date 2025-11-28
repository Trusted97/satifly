<?php

namespace App\Tests\Process;

use App\Process\EnvironmentProvider;
use App\Process\ProcessFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ProcessFactoryTest extends TestCase
{
    public function testCreateReturnsConfiguredProcess(): void
    {
        $env = ['A' => 'B'];

        $provider = $this->createMock(EnvironmentProvider::class);
        $provider->method('getEnv')->willReturn($env);

        $factory = new ProcessFactory('/root', $provider);

        $process = $factory->create(['bin/tool', 'arg1', 'arg2'], 123);

        $this->assertInstanceOf(Process::class, $process);
        $this->assertSame('\'/root/bin/tool\' \'arg1\' \'arg2\'', $process->getCommandLine());
        $this->assertSame('/root', $process->getWorkingDirectory());
        $this->assertSame($env, $process->getEnv());
        $this->assertSame(123.0, $process->getTimeout());
    }

    public function testCreateThrowsOnEmptyCommand(): void
    {
        $provider = $this->createMock(EnvironmentProvider::class);
        $factory  = new ProcessFactory('/root', $provider);

        $this->expectException(\InvalidArgumentException::class);

        $factory->create([]);
    }

    public function testCommandIsPrefixedWithRootPath(): void
    {
        $provider = $this->createMock(EnvironmentProvider::class);
        $provider->method('getEnv')->willReturn([]);

        $factory = new ProcessFactory('/abc', $provider);

        $process = $factory->create(['vendor/bin/satis']);

        $this->assertSame('\'/abc/vendor/bin/satis\'', $process->getCommandLine());
    }
}
