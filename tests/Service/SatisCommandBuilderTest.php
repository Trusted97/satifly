<?php

namespace App\Tests\Service;

use App\Service\SatisCommandBuilder;
use PHPUnit\Framework\TestCase;

final class SatisCommandBuilderTest extends TestCase
{
    public function testFromCreatesInstance(): void
    {
        $builder = SatisCommandBuilder::from('satis.json', 'output');

        $this->assertSame('satis.json', $builder->configFile);
        $this->assertSame('output', $builder->outputDir);
    }

    public function testWithRepositoryAddsRepositoryNameToOptions(): void
    {
        $builder = SatisCommandBuilder::from('satis.json', 'output')
            ->withRepository('my-repo');

        $command = $builder->build();

        $this->assertContains('my-repo', $command);
    }

    public function testAddOptionAndAddOptionsMergeOptions(): void
    {
        $builder = SatisCommandBuilder::from('satis.json', 'output');
        $builder->addOptions(['--option1', '--option2']);

        $command = $builder->build();

        $this->assertContains('--option1', $command);
        $this->assertContains('--option2', $command);
        $this->assertContains('--skip-errors', $command);
        $this->assertContains('--no-ansi', $command);
        $this->assertContains('--verbose', $command);
    }

    public function testAddArgsAddsExtraArguments(): void
    {
        $builder = SatisCommandBuilder::from('satis.json', 'output');
        $builder->addArgs(['arg1', 'arg2']);

        $command = $builder->build();

        $this->assertContains('arg1', $command);
        $this->assertContains('arg2', $command);
    }

    public function testBuildReturnsFullCommandArray(): void
    {
        $builder = SatisCommandBuilder::from('satis.json', 'output')
            ->withRepository('my-repo')
            ->addOptions(['--option'])
            ->addArgs(['arg']);

        $command = $builder->build();

        $this->assertSame('vendor/bin/satis', $command[0]);
        $this->assertSame('build', $command[1]);
        $this->assertSame('satis.json', $command[2]);
        $this->assertSame('output', $command[3]);
        $this->assertContains('my-repo', $command);
        $this->assertContains('--option', $command);
        $this->assertContains('arg', $command);
    }
}
