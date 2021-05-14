<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use UserFrosting\Sprinkle\Core\Bakery\BakeCommand;
use UserFrosting\Sprinkle\Core\Bakery\BuildAssets;
use UserFrosting\Sprinkle\Core\Bakery\ClearCacheCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugCommand;
use UserFrosting\Sprinkle\Core\Bakery\LocaleCompareCommand;
use UserFrosting\Sprinkle\Core\Bakery\LocaleDictionaryCommand;
use UserFrosting\Sprinkle\Core\Bakery\LocaleInfoCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateRefreshCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateResetCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateRollbackCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateStatusCommand;
use UserFrosting\Sprinkle\Core\Bakery\RouteListCommand;
use UserFrosting\Sprinkle\Core\Bakery\SeedCommand;
use UserFrosting\Sprinkle\Core\Bakery\SeedListCommand;
use UserFrosting\Sprinkle\Core\Bakery\SetupCommand;
use UserFrosting\Sprinkle\Core\Bakery\SetupDbCommand;
use UserFrosting\Sprinkle\Core\Bakery\SetupEnvCommand;
use UserFrosting\Sprinkle\Core\Bakery\SetupSmtpCommand;
use UserFrosting\Sprinkle\Core\Bakery\SprinkleListCommand;
use UserFrosting\Sprinkle\Core\Bakery\Test;
use UserFrosting\Sprinkle\Core\Bakery\TestMailCommand;
use UserFrosting\Sprinkle\SprinkleReceipe;

class Core implements SprinkleReceipe
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Core Sprinkle';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return __DIR__;
    }

    /**
     * {@inheritdoc}
     */
    public function getBakeryCommands(): array
    {
        return [
            new BakeCommand(),
            new BuildAssets(),
            new ClearCacheCommand(),
            new DebugCommand(),
            new LocaleCompareCommand(),
            new LocaleDictionaryCommand(),
            new LocaleInfoCommand(),
            new MigrateCommand(),
            new MigrateRefreshCommand(),
            new MigrateResetCommand(),
            new MigrateRollbackCommand(),
            new MigrateStatusCommand(),
            new RouteListCommand(),
            new SeedCommand(),
            new SeedListCommand(),
            new SetupCommand(),
            new SetupDbCommand(),
            new SetupEnvCommand(),
            new SetupSmtpCommand(),
            new SprinkleListCommand(),
            new Test(),
            new TestMailCommand(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSprinkles(): array
    {
        return [];
    }
}
