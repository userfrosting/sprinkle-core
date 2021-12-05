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
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\Core\Bakery\Helper\DatabaseTest;
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;
use UserFrosting\Sprinkle\Core\Validators\NodeVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\NpmVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpDeprecationValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpVersionValidator;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Debug CLI tool.
 */
class DebugCommand extends Command
{
    use DatabaseTest;
    use WithSymfonyStyle;

    /** @Inject */
    protected Config $config;

    /** @Inject */
    protected SprinkleManager $sprinkleManager;

    /** @Inject */
    protected PhpVersionValidator $phpVersionValidator;

    /** @Inject */
    protected PhpDeprecationValidator $phpDeprecationValidator;

    /** @Inject */
    protected NodeVersionValidator $nodeVersionValidator;

    /** @Inject */
    protected NpmVersionValidator $npmVersionValidator;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('debug')
             ->setDescription('Test the UserFrosting installation and setup of the database')
             ->setHelp("This command is used to check if the various dependencies of UserFrosting are met and display useful debugging information. \nIf any error occurs, check out the online documentation for more info about that error. \nThis command also provide the necessary tools to test the setup of the database credentials");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title('UserFrosting');

        // Validate PHP, Node and Npm versions.
        $this->validateVersions();

        // Perform tasks & display info
        $this->io->definitionList(
            ['Framework version'  => \Composer\InstalledVersions::getPrettyVersion('userfrosting/framework')],
            ['OS Name'            => php_uname('s')],
            ['Main Sprinkle Path' => $this->sprinkleManager->getMainSprinkle()->getPath()],
            ['Environment mode'   => env('UF_MODE', 'default')],
            ['PHP Version'        => $this->phpVersionValidator->getInstalled()],
            ['Node Version'       => $this->nodeVersionValidator->getInstalled()],
            ['NPM Version'        => $this->npmVersionValidator->getInstalled()]
        );

        // Now we list Sprinkles
        $this->listSprinkles($input, $output);

        // Show the DB config
        $this->showConfig();

        // Check database connection
        $this->checkDatabase();

        // Show child debug commands on verbose mode
        if ($this->io->isVerbose()) {
            $command = $this->getApplication()->find('debug:locator');
            $command->run($input, $output);

            $command = $this->getApplication()->find('debug:events');
            $command->run($input, $output);
        }

        // If all went well and there's no fatal errors, we are ready to bake
        $this->io->success('Ready to bake !');

        // Command return success
        return self::SUCCESS;
    }

    /**
     * Validate PHP, Node and Npm versions.
     */
    protected function validateVersions(): void
    {
        // Validate PHP, Node and npm version
        try {
            $this->phpVersionValidator->validate();
            $this->nodeVersionValidator->validate();
            $this->npmVersionValidator->validate();
        } catch (VersionCompareException $e) {
            $this->io->error($e->getMessage());
            exit(1);
        }

        // Validate deprecated versions
        try {
            $this->phpDeprecationValidator->validate();
        } catch (VersionCompareException $e) {
            $this->io->warning($e->getMessage());
        }
    }

    /**
     * List all sprinkles defined in the Sprinkles schema file.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function listSprinkles(InputInterface $input, OutputInterface $output): void
    {
        $command = $this->getApplication()->find('sprinkle:list');
        $command->run($input, $output);
    }

    /**
     * Check the database connection and setup the `.env` file if we can't
     * connect and there's no one found.
     */
    protected function checkDatabase(): void
    {
        $this->io->title('Testing database connection...');

        try {
            $this->testDB();
            $this->io->success('Database connection successful');

            return;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->io->error($error);
            exit(1);
        }
    }

    /**
     * Display database config as for debug purposes.
     */
    protected function showConfig(): void
    {
        // Get connection
        $connection = $this->config->get('db.default');

        // Display database info
        $this->io->title('Database config');
        $this->io->definitionList(
            ['CONNECTION'   => $connection],
            new TableSeparator(),
            ['DRIVER'       => $this->config->get('db.connections.' . $connection . '.driver')],
            ['HOST'         => $this->config->get('db.connections.' . $connection . '.host')],
            ['PORT'         => $this->config->get('db.connections.' . $connection . '.port')],
            ['DATABASE'     => $this->config->get('db.connections.' . $connection . '.database')],
            ['USERNAME'     => $this->config->get('db.connections.' . $connection . '.username')],
            ['PASSWORD'     => ($this->config->get('db.connections.' . $connection . '.password') ? '*********' : '')]
        );
    }
}
