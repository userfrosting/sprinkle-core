<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use DI\Attribute\Inject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;
use UserFrosting\Sprinkle\Core\Validators\NodeVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\NpmVersionValidator;

/**
 * Alias for common used webpack command, for integration into `bake` command.
 */
final class WebpackCommand extends Command
{
    use WithSymfonyStyle;

    #[Inject]
    protected NodeVersionValidator $nodeVersionValidator;

    #[Inject]
    protected NpmVersionValidator $npmVersionValidator;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $help = [
            'This command will install <info>npm</info> dependencies locally and run <info>Webpack Encore</info>.',
            'It can be use it to automatically download and install the assets in the build directory.',
            'Everything will be executed in the same dir the bakery command is executed.',
            'For more info, see <comment>https://learn.userfrosting.com/basics/installation</comment>',
        ];

        $this->setName('webpack')
             ->setDescription('Build the assets using npm and Webpack Encore')
             ->setHelp(implode(' ', $help))
             ->addOption('production', 'p', InputOption::VALUE_NONE, 'On deploy, create a production build');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get options
        $production = (bool) $input->getOption('production');

        // Validate dependencies
        try {
            $this->nodeVersionValidator->validate();
            $this->npmVersionValidator->validate();
        } catch (VersionCompareException $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }

        // Get path
        $path = getcwd();
        if ($path === false) {
            $this->io->error('Error getting working directory');

            return self::FAILURE;
        }

        // Install NPM
        $this->io->section('Installing npm Dependencies');
        $file = $path . '/package.json';
        if (!file_exists($file)) {
            $this->io->warning("$file not found. Skipping.");
        } else {
            // Execute command
            if ($this->executeCommand('npm install') !== 0) {
                $this->io->error('npm dependency installation has failed');

                return self::FAILURE;
            }
        }

        // Execute Webpack
        $this->io->title('Running Webpack Encore');
        $file = $path . '/webpack.config.js';
        if (!file_exists($file)) {
            $this->io->warning("$file not found. Skipping.");
        } else {
            // Execute command
            $command = ($production) ? 'npm run build' : 'npm run dev';
            if ($this->executeCommand($command) !== 0) {
                $this->io->error('Webpack Encore run has failed');

                return self::FAILURE;
            }
        }

        // If all went well and there's no fatal errors, we are successful
        $this->io->success('Assets Compiled');

        return self::SUCCESS;
    }

    /**
     * Execute shell command and return exit code.
     *
     * @param string $command Shell command to execute
     *
     * @return int Return code from passthru
     */
    private function executeCommand(string $command): int
    {
        $this->io->writeln("> <comment>$command</comment>");
        $exitCode = 0;
        passthru($command, $exitCode);

        return $exitCode;
    }
}
