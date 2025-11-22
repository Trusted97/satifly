<?php

namespace App\Command;

use Composer\Json\JsonFile;
use Composer\Satis\Console\Application;
use Seld\JsonLint\ParsingException;
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
    name: 'satifly:build',
    description: 'Build composer packages using Satis, with optional caching'
)]
class SatiflyBuildCommand extends Command
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
            )
            ->addOption(
                'no-cache',
                null,
                InputOption::VALUE_NONE,
                'Ignore cache and force a full rebuild'
            );
    }

    /**
     * @throws ParsingException
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $verbose = $output->isVerbose() || $output->isVeryVerbose() || $output->isDebug();

        $io->title('⚙️  Starting Satis build process...');
        $configFile = $this->parameterBag->get('satis_filename');
        $lifetime   = (int) $input->getOption('lifetime');
        $noCache    = (bool) $input->getOption('no-cache');
        $outputDir  = $input->getArgument('output-dir');

        $io->writeln("📄 Using config file: <info>{$configFile}</info>");

        if ($outputDir) {
            $io->writeln("📦 Output directory: <info>{$outputDir}</info>");
        }

        // ---- VERBOSE DEBUG INFO ----
        if ($verbose) {
            $io->writeln("🔎 Verbose mode enabled");
            $io->writeln("🔧 Lifetime: " . ($lifetime ?: "none"));
            $io->writeln("⛔ No Cache Option: " . ($noCache ? "yes" : "no"));
        }

        // ---- CACHING LOGIC ----
        if (!$noCache && !empty($lifetime) && \is_file($configFile)) {
            if (!$outputDir) {
                $file      = new JsonFile($configFile);
                $config    = $file->read();
                $outputDir = $config['output-dir'] ?? null;

                $io->writeln("📁 Resolved output directory: <info>{$outputDir}</info>");
            }

            $modifiedAt = \filemtime($configFile);
            $lastUpdate = @\filemtime($outputDir . '/packages.json');

            if ($verbose) {
                $io->writeln("🕒 Config file last modified: {$modifiedAt}");
                $io->writeln("📦 packages.json last update: " . ($lastUpdate ?: "not found"));
            }

            if ($lastUpdate && $modifiedAt < $lastUpdate && \time() - $lastUpdate < $lifetime) {
                $io->success('✅ Cache is still valid — skipping build.');
                return 0;
            }

            if ($verbose) {
                $io->writeln("⚠️ Cache expired, triggering rebuild");
            }
        }

        if ($noCache) {
            $io->writeln('⛔ Cache ignored due to <info>--no-cache</info> option.');
        }

        // ---- EXECUTE SATIS BUILD ----
        $io->section('🚀 Building Satis repository...');
        $startTime = \microtime(true);

        if ($verbose) {
            $io->writeln("📂 Running Satis build with:");
            $io->writeln("- file: {$configFile}");
            $io->writeln("- output-dir: " . ($outputDir ?: "null"));
            $io->writeln("- packages: " . \json_encode($input->getArgument('packages'), JSON_THROW_ON_ERROR));
        }

        $satisInput = new ArrayInput([
            'command'       => 'build',
            'file'          => $configFile,
            'output-dir'    => $outputDir,
            'packages'      => $input->getArgument('packages'),
            '--skip-errors' => true,
        ]);

        $exitCode = (new Application())->doRun($satisInput, $output);

        $duration = \microtime(true) - $startTime;
        $formattedTime = \number_format($duration, 2);

        if (Command::SUCCESS === $exitCode) {
            $io->success("🎉 Satis build completed successfully in ⏱️ {$formattedTime}s !");
        } else {
            $io->error('❌ Satis build failed. Check the output above for details.');
        }

        return $exitCode;
    }
}
