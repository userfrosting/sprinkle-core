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
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\Core\Seeder\SeedRepositoryInterface;

/**
 * seed Bakery Command
 * Perform a database seed.
 */
class SeedListCommand extends Command
{
    use WithSymfonyStyle;

    #[Inject]
    protected SeedRepositoryInterface $seeds;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('seed:list')
             ->setDescription('List all seeds available')
             ->setHelp('This command returns a list of seeds that can be called using the `seed` command.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Seeds List');
        $seeds = $this->seeds->list();
        if (empty($seeds)) {
            $this->io->note('No seeds founds');
        } else {
            $this->io->listing($seeds);
        }

        return self::SUCCESS;
    }
}
