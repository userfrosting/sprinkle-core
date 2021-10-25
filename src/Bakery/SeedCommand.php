<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\Core\Seeder\SeedRepositoryInterface;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * seed Bakery Command
 * Perform a database seed.
 */
class SeedCommand extends Command
{
    use WithSymfonyStyle;

    /** @Inject */
    protected SeedRepositoryInterface $seeds;

    /** @Inject */
    protected Config $config;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('seed')
             ->setDescription('Seed the database with records')
             ->setHelp('This command runs a seed to populate the app with default, random and/or test data.')
             ->addArgument('class', InputArgument::IS_ARRAY, 'The class name of the seeder. Separate multiple seeder with a space.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run without confirmation.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Seeder');

        // Get options
        $classes = $input->getArgument('class');
        $force = $input->getOption('force');

        // If class is empty, ask to choose one.
        if (empty($classes)) {

            // Abort if no registered seeds
            if (empty($this->seeds->list())) {
                $this->io->warning('No available seeds founds');

                return self::SUCCESS;
            }

            $classes = [$this->selectSeed()];
        }

        // Validate each classes
        foreach ($classes as $className) {
            if (!$this->seeds->has($className)) {
                $this->io->error("Class is not a valid seed : " . $className);

                return self::FAILURE;
            }
        }

        // Display what's about to be run
        if ($this->config->get('bakery.confirm_sensitive_command') || $this->io->isVerbose()) {
            $this->io->section('Seed(s) to apply');
            $this->io->listing($classes);
        }

        // Confirm action if required (for example in production mode).
        if ($this->config->get('bakery.confirm_sensitive_command') && !$force) {
            if (!$this->io->confirm('Do you really wish to continue ?', false)) {
                return self::SUCCESS;
            }
        }

        // Run seeds
        foreach ($classes as $seed) {
            try {
                $this->seeds->get($seed)->run();
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());

                return self::FAILURE;
            }
        }

        // Success
        $this->io->success('Seed successful !');

        return self::SUCCESS;
    }

    protected function selectSeed(): string
    {
        return $this->io->choice('Select seed to run', $this->seeds->list());
    }
}
