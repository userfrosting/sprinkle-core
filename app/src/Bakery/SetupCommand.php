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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Setup wizard CLI Tools.
 * Helper command to setup .env file.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SetupCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup')
             ->setDescription('UserFrosting Configuration Wizard')
             ->setHelp('This command combine the <info>setup:env</info>, <info>setup:db</info> and <info>setup:mail</info> commands.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('setup:db');
        $command->run($input, $output);

        $command = $this->getApplication()->find('setup:mail');
        $command->run($input, $output);

        $command = $this->getApplication()->find('setup:env');
        $command->run($input, $output);

        return self::SUCCESS;
    }
}