<?php

namespace App\Command;

use Composer\Json\JsonFile;
use Composer\Satis\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'satifly:rebuild',
    description: 'Rebuild composer packages when config is changed or definitions is outdated'
)]
class RebuildCommand extends Command
{
    public function __construct(public ParameterBagInterface $parameterBag)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'Json file to use', './satis.json')
            ->addArgument('output-dir', InputArgument::OPTIONAL, 'Location where to output built files', null)
            ->addArgument(
                'packages',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Packages that should be built. If not provided, all packages are built.',
                null
            )
            ->addOption(
                'lifetime',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Maximum lifetime of composer definitions in seconds'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('⚙️  Starting Satis rebuild process...');
        $configFile = $this->parameterBag->get('satis_filename');
        $lifetime   = (int) $input->getOption('lifetime');
        $outputDir  = $input->getArgument('output-dir');

        $io->writeln("📄 Using config file: <info>{$configFile}</info>");

        if ($outputDir) {
            $io->writeln("📦 Output directory: <info>{$outputDir}</info>");
        }

        if (!empty($lifetime) && \is_file($configFile)) {
            if (!$outputDir) {
                $file      = new JsonFile($configFile);
                $config    = $file->read();
                $outputDir = $config['output-dir'] ?? null;
                $io->writeln("📁 Resolved output directory: <info>{$outputDir}</info>");
            }

            $modifiedAt = \filemtime($configFile);
            $lastUpdate = @\filemtime($outputDir . '/packages.json');
            if ($modifiedAt < $lastUpdate && \time() - $lastUpdate < $lifetime) {
                $io->success('✅ Cache is still valid — skipping rebuild.');

                return 0;
            }
        }

        $io->section('🚀 Building Satis repository...');
        $startTime  = \microtime(true);
        $satisInput = new ArrayInput([
            'command'       => 'build',
            'file'          => $configFile,
            'output-dir'    => $outputDir,
            'packages'      => $input->getArgument('packages'),
            '--skip-errors' => true,
        ]);

        $exitCode = (new Application())->doRun($satisInput, $output);

        $duration      = \microtime(true) - $startTime;
        $formattedTime = \number_format($duration, 2);

        if (Command::SUCCESS === $exitCode) {
            $io->success("🎉 Satis rebuild completed successfully in ⏱️ {$formattedTime}s !");
        } else {
            $io->error('❌ Satis rebuild failed. Check the output above for details.');
        }

        return $exitCode;
    }
}
