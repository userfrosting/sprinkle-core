<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;

/**
 * migrate:status Bakery Command
 * Show the list of installed and pending migration.
 */
class MigrateStatusCommand extends Command
{
    use WithSymfonyStyle;

    /** @Inject */
    protected Migrator $migrator;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:status')
             ->setDescription('Show the list of installed and pending migration.')
             ->setHelp('Show the list of installed and pending migration. This command also show if an installed migration is available in the Filesystem, so it can be run down by the rollback command')
             ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The database connection to use.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Migration status');

        // Set connection to the selected database
        $this->migrator->setConnection($input->getOption('database'));

        // Get ran migrations. If repository doesn't exist, there's no ran
        if (!$this->migrator->repositoryExists()) {
            $ran = collect();
        } else {
            $ran = $this->migrator->getRepository()->getMigrations();
        }

        // Get available migrations and calculate pending one
        $available = $this->migrator->getAvailableMigrations();
        $pending = $this->migrator->getPendingMigrations();

        // Display ran migrations
        $this->io->section('Installed migrations');
        if ($ran->count() > 0) {
            $this->io->table(
                ['Migration', 'Available?', 'Batch'],
                $this->getStatusFor($ran, $available)
            );
        } else {
            $this->io->note('No installed migrations');
        }

        // Display pending migrations
        $this->io->section('Pending migrations');
        if (count($pending) > 0) {
            $this->io->listing($pending);
        } else {
            $this->io->note('No pending migrations');
        }

        return self::SUCCESS;
    }

    /**
     * Return an array of [migration, available] association.
     * A migration is available if it's in the available stack (class is in the Filesystem).
     *
     * @param Collection $ran       The ran migrations
     * @param array      $available The available migrations
     *
     * @return array An array of [migration, available] association
     */
    protected function getStatusFor(Collection $ran, array $available)
    {
        return collect($ran)->map(function ($migration) use ($available) {
            if (in_array($migration->migration, $available)) {
                $available = '<info>Yes</info>';
            } else {
                $available = '<fg=red>No</fg=red>';
            }

            return [$migration->migration, $available, $migration->batch];
        })->toArray();
    }
}
