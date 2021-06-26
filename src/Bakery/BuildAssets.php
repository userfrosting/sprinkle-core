<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;
use Symfony\Component\Console\Command\Command;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\Core\Validators\NodeVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\NpmVersionValidator;
use UserFrosting\Sprinkle\SprinkleManager;

/**
 * Assets builder CLI Tools.
 * Wrapper for npm/node commands.
 */
class BuildAssets extends Command
{
    use WithSymfonyStyle;

    /** @Inject */
    protected NodeVersionValidator $nodeVersionValidator;

    /** @Inject */
    protected NpmVersionValidator $npmVersionValidator;

    /** @Inject */
    protected SprinkleManager $sprinkleManager;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('build-assets')
             ->setDescription('Build the assets using node and npm')
             ->setHelp('The build directory contains the scripts and configuration files required to download Javascript, CSS, and other assets used by UserFrosting. This command will install Gulp, Bower, and several other required npm packages locally. With <info>npm</info> set up with all of its required packages, it can be use it to automatically download and install the assets in the correct directories. For more info, see <comment>https://learn.userfrosting.com/basics/installation</comment>')
             ->addOption('compile', 'c', InputOption::VALUE_NONE, 'Compile the assets and asset bundles for production environment')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force assets compilation by deleting cached data and installed assets before proceeding');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Display header,
        $this->io->title("UserFrosting's Assets Builder");

        // Validate Node and npm version
        try {
            $this->nodeVersionValidator->validate();
            $this->npmVersionValidator->validate();
        } catch (VersionCompareException $e) {
            $this->io->error($e->getMessage());
            exit(1);
        }

        // Delete cached data is requested
        if ($input->getOption('force')) {
            $this->clean();
        }

        // Perform tasks
        $this->npmInstall($input->getOption('force'));
        $this->assetsInstall();

        // Compile if requested
        // TODO Reimplement production status
        if ($input->getOption('compile')/* || $this->isProduction()*/) {
            $this->buildAssets();
        }

        // If all went well and there's no fatal errors, we are successful
        $this->io->success('Assets install looks successful, check output for specifics');

        return self::SUCCESS;
    }

    /**
     * Install npm package.
     *
     * @param bool $force Force `npm install` to be run, ignoring evidence of a previous run.
     */
    protected function npmInstall(bool $force): void
    {
        $this->io->section('Installing npm dependencies');
        $this->io->writeln('> <comment>npm install</comment>');

        // Temporarily change the working directory so we can install npm dependencies
        $wd = getcwd();
        chdir($this->getBuildPath());

        // Delete troublesome package-lock.json
        if (file_exists('package-lock.json')) {
            unlink('package-lock.json');
        }

        // Skip if lockfile indicates previous run
        if (!$force && file_exists('package.lock') && filemtime('package.json') < filemtime('package.lock') - 1) {
            $this->io->writeln('Skipping as package-lock.json age indicates dependencies are already installed');
            chdir($wd);

            return;
        }

        // Ensure extraneous dependencies are cleared out
        $exitCode = 0;
        passthru('npm prune');

        if ($exitCode !== 0) {
            $this->io->error('npm dependency installation has failed');
            exit(1);
        }

        // Install the new dependencies
        $exitCode = 0;
        passthru('npm install', $exitCode);

        if ($exitCode !== 0) {
            $this->io->error('npm dependency installation has failed');
            exit(1);
        }

        // Update lockfile date (for skip logic)
        touch('package.lock');

        chdir($wd);
    }

    /**
     * Perform UF Assets installation.
     */
    protected function assetsInstall(): void
    {
        $this->io->section('Installing frontend vendor assets');
        $this->io->writeln('> <comment>npm run uf-assets-install</comment>');

        // Temporarily change the working directory (more reliable than --prefix npm switch)
        $wd = getcwd();
        chdir($this->getBuildPath());
        $exitCode = 0;
        passthru('npm run uf-assets-install', $exitCode);
        chdir($wd);

        if ($exitCode !== 0) {
            $this->io->error('assets installation has failed');
            exit($exitCode);
        }
    }

    /**
     * Build the production bundle.
     */
    protected function buildAssets(): void
    {
        $this->io->section('Building assets for production');

        // Temporarily change the working directory (more reliable than --prefix npm switch)
        $wd = getcwd();
        chdir($this->getBuildPath());

        $exitCode = 0;

        $this->io->writeln('> <comment>npm run uf-bundle</comment>');
        passthru('npm run uf-bundle');

        if ($exitCode !== 0) {
            $this->io->error('bundling has failed');
            exit($exitCode);
        }

        chdir($wd);
    }

    /**
     * Run the `uf-clean` command to delete installed assets, delete compiled
     * bundle config file and delete compiled assets.
     */
    protected function clean(): void
    {
        $this->io->section('Cleaning cached data');
        $this->io->writeln('> <comment>npm run uf-clean</comment>');

        // Temporarily change the working directory (more reliable than --prefix npm switch)
        $wd = getcwd();
        chdir($this->getBuildPath());
        $exitCode = 0;
        passthru('npm run uf-clean', $exitCode);
        chdir($wd);

        if ($exitCode !== 0) {
            $this->io->error('Failed to clean cached data');
            exit($exitCode);
        }
    }

    /**
     * Return path where to place the build artifacts.
     *
     * @return string
     */
    protected function getBuildPath(): string
    {
        // TODO : See if locator could be better fit here.
        //        Another way / place for the build dir seams required.
        return $this->sprinkleManager->getMainSprinkle()::getPath() . '\..\build'; //  \UserFrosting\DS . \UserFrosting\BUILD_DIR_NAME;
    }
}
