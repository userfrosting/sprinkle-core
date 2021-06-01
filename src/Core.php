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
use UserFrosting\Sprinkle\Core\Routes\AlertsRoutes;
use UserFrosting\Sprinkle\Core\Routes\AssetsRoutes;
use UserFrosting\Sprinkle\Core\ServicesProvider\AlertStreamService;
use UserFrosting\Sprinkle\Core\ServicesProvider\AssetService;
use UserFrosting\Sprinkle\Core\ServicesProvider\CacheService;
use UserFrosting\Sprinkle\Core\ServicesProvider\ConfigService;
use UserFrosting\Sprinkle\Core\ServicesProvider\CsrfService;
use UserFrosting\Sprinkle\Core\ServicesProvider\DbService;
use UserFrosting\Sprinkle\Core\ServicesProvider\ErrorHandlerService;
use UserFrosting\Sprinkle\Core\ServicesProvider\FactoryService;
use UserFrosting\Sprinkle\Core\ServicesProvider\FilesystemService;
use UserFrosting\Sprinkle\Core\ServicesProvider\LocaleService;
use UserFrosting\Sprinkle\Core\ServicesProvider\LocatorService;
use UserFrosting\Sprinkle\Core\ServicesProvider\LoggersService;
use UserFrosting\Sprinkle\Core\ServicesProvider\MailService;
use UserFrosting\Sprinkle\Core\ServicesProvider\MigratorService;
use UserFrosting\Sprinkle\Core\ServicesProvider\RouterService;
use UserFrosting\Sprinkle\Core\ServicesProvider\SeederService;
use UserFrosting\Sprinkle\Core\ServicesProvider\SessionService;
use UserFrosting\Sprinkle\Core\ServicesProvider\ThrottlerService;
use UserFrosting\Sprinkle\Core\ServicesProvider\TranslatorService;
use UserFrosting\Sprinkle\Core\ServicesProvider\TwigService;
use UserFrosting\Sprinkle\SprinkleReceipe;

class Core implements SprinkleReceipe
{
    /**
     * {@inheritdoc}
     */
    public static function getName(): string
    {
        return 'Core Sprinkle';
    }

    /**
     * {@inheritdoc}
     */
    public static function getPath(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * {@inheritdoc}
     */
    public static function getBakeryCommands(): array
    {
        return [
            BakeCommand::class,
            BuildAssets::class,
            ClearCacheCommand::class,
            DebugCommand::class,
            LocaleCompareCommand::class,
            LocaleDictionaryCommand::class,
            LocaleInfoCommand::class,
            MigrateCommand::class,
            MigrateRefreshCommand::class,
            MigrateResetCommand::class,
            MigrateRollbackCommand::class,
            MigrateStatusCommand::class,
            RouteListCommand::class,
            SeedCommand::class,
            SeedListCommand::class,
            SetupCommand::class,
            SetupDbCommand::class,
            SetupEnvCommand::class,
            SetupSmtpCommand::class,
            SprinkleListCommand::class,
            Test::class,
            TestMailCommand::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSprinkles(): array
    {
        return [];
    }

    /**
     * Returns a list of routes definition in PHP files.
     *
     * @return string[]
     */
    public static function getRoutes(): array
    {
        return [
            AlertsRoutes::class,
            AssetsRoutes::class,
        ];
    }

    /**
     * Returns a list of all PHP-DI services/container definitions files.
     *
     * @return string[]
     */
    public static function getServices(): array
    {
        return [
            AlertStreamService::class,
            AssetService::class,
            CacheService::class,
            ConfigService::class,
            // CsrfService::class,
            DbService::class,
            ErrorHandlerService::class,
            // FactoryService::class,
            FilesystemService::class,
            LocaleService::class,
            LocatorService::class,
            LoggersService::class,
            // MailService::class,
            MigratorService::class,
            // RouterService::class,
            SeederService::class,
            SessionService::class,
            // ThrottlerService::class,
            TranslatorService::class,
            TwigService::class,
        ];
    }
}
