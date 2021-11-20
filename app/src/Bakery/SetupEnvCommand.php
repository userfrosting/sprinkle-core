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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Support\DotenvEditor\DotenvEditor;

/**
 * Setup wizard CLI Tools.
 * Helper command to setup the 'UF_MODE' var of the .env file.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SetupEnvCommand extends Command
{
    /**
     * @var string Path to the .env file
     */
    // TODO : Use locator base path
    // protected $envPath = \UserFrosting\APP_DIR . '/.env';
    protected $envPath = '.env';

    /**
     * @var string Key for the env mode setting
     */
    protected $modeKey = 'UF_MODE';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:env')
             ->setDescription('UserFrosting Environment Configuration Wizard')
             ->setHelp('Helper command to setup environment mode. This can also be done manually by editing the <comment>app/.env</comment> file or using global server environment variables.')
             ->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'The environment to use');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title("UserFrosting's Environment Setup Wizard");
        $this->io->note("Environment mode will be saved in `{$this->envPath}`");
        $this->io->write('Select desired environment mode. Production should only be used when deploying a live app.');

        // Get an instance of the DotenvEditor
        // TODO : Use locator base path
        // TODO : Move dotenvEditor to services
        // $dotenvEditor = new DotenvEditor(\UserFrosting\APP_DIR, false);
        $dotenvEditor = new DotenvEditor('./', false);
        $dotenvEditor->load($this->envPath);

        // Ask for mode
        $newEnvMode = $this->askForEnv($input);

        // Save new value
        $dotenvEditor->setKey($this->modeKey, $newEnvMode);
        $dotenvEditor->save();

        // Success
        $this->io->success("Environment mode successfully changed to `$newEnvMode` in `{$this->envPath}`");

        return self::SUCCESS;
    }

    /**
     * Ask for env mode.
     *
     * @param InputInterface $args Command arguments
     *
     * @return string The new env mode
     */
    protected function askForEnv(InputInterface $args)
    {
        // Ask for mode if not defined in command arguments
        if ($args->getOption('mode')) {
            return $args->getOption('mode');
        } else {
            $newEnvMode = $this->io->choice('Environment Mode', [
                'default',
                'production',
                'debug',
                'Other...',
            ], 'default');
        }

        // Ask for manual input if 'other' was chosen
        if ($newEnvMode == 'Other...') {
            $newEnvMode = $this->io->ask('Enter desired environment mode');
        }

        return $newEnvMode;
    }
}