<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use Slim\Views\TwigMiddleware;
use UserFrosting\Sprinkle\Core\Bakery\BakeCommand;
use UserFrosting\Sprinkle\Core\Bakery\BuildAssets;
use UserFrosting\Sprinkle\Core\Bakery\ClearCacheCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugLocatorCommand;
use UserFrosting\Sprinkle\Core\Bakery\LocaleCompareCommand;
use UserFrosting\Sprinkle\Core\Bakery\LocaleDictionaryCommand;
use UserFrosting\Sprinkle\Core\Bakery\LocaleInfoCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateCleanCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateRefreshCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateResetCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateResetHardCommand;
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
use UserFrosting\Sprinkle\Core\Bakery\TestMailCommand;
use UserFrosting\Sprinkle\Core\Database\Migrations\v400\SessionsTable;
use UserFrosting\Sprinkle\Core\Database\Migrations\v400\ThrottlesTable;
use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerMiddleware;
use UserFrosting\Sprinkle\Core\Middlewares\LocaleMiddleware;
use UserFrosting\Sprinkle\Core\Middlewares\SessionMiddleware;
use UserFrosting\Sprinkle\Core\Routes\AlertsRoutes;
use UserFrosting\Sprinkle\Core\Routes\AssetsRoutes;
use UserFrosting\Sprinkle\Core\ServicesProvider\AlertStreamService;
use UserFrosting\Sprinkle\Core\ServicesProvider\AssetService;
use UserFrosting\Sprinkle\Core\ServicesProvider\CacheService;
use UserFrosting\Sprinkle\Core\ServicesProvider\ConfigService;
use UserFrosting\Sprinkle\Core\ServicesProvider\CsrfService;
use UserFrosting\Sprinkle\Core\ServicesProvider\DatabaseService;
use UserFrosting\Sprinkle\Core\ServicesProvider\ErrorHandlerService;
use UserFrosting\Sprinkle\Core\ServicesProvider\FactoryService;
use UserFrosting\Sprinkle\Core\ServicesProvider\I18nService;
use UserFrosting\Sprinkle\Core\ServicesProvider\LocatorService;
use UserFrosting\Sprinkle\Core\ServicesProvider\LoggersService;
use UserFrosting\Sprinkle\Core\ServicesProvider\MailService;
use UserFrosting\Sprinkle\Core\ServicesProvider\MigratorService;
use UserFrosting\Sprinkle\Core\ServicesProvider\RoutingService;
use UserFrosting\Sprinkle\Core\ServicesProvider\SeedService;
use UserFrosting\Sprinkle\Core\ServicesProvider\SessionService;
use UserFrosting\Sprinkle\Core\ServicesProvider\ThrottlerService;
use UserFrosting\Sprinkle\Core\ServicesProvider\TwigService;
use UserFrosting\Sprinkle\Core\ServicesProvider\VersionsService;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\LocatorRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\Core\Twig\Extensions\AlertsExtension;
use UserFrosting\Sprinkle\Core\Twig\Extensions\AssetsExtension;
use UserFrosting\Sprinkle\Core\Twig\Extensions\CoreExtension;
use UserFrosting\Sprinkle\Core\Twig\Extensions\CsrfExtension;
use UserFrosting\Sprinkle\Core\Twig\Extensions\I18nExtension;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\UniformResourceLocator\ResourceStream;

class Core implements SprinkleRecipe, TwigExtensionRecipe, MigrationRecipe, LocatorRecipe
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
            DebugLocatorCommand::class,
            LocaleCompareCommand::class,
            LocaleDictionaryCommand::class,
            LocaleInfoCommand::class,
            MigrateCommand::class,
            MigrateCleanCommand::class,
            MigrateRefreshCommand::class,
            MigrateResetCommand::class,
            MigrateResetHardCommand::class,
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
            DatabaseService::class,
            ErrorHandlerService::class,
            // FactoryService::class,
            I18nService::class,
            LocatorService::class,
            LoggersService::class,
            // MailService::class,
            MigratorService::class,
            RoutingService::class,
            SeedService::class,
            SessionService::class,
            // ThrottlerService::class,
            TwigService::class,
            VersionsService::class,
        ];
    }

    /**
     * Returns a list of all Middlewares classes.
     *
     * @return \Psr\Http\Server\MiddlewareInterface[]
     */
    public static function getMiddlewares(): array
    {
        return [
            LocaleMiddleware::class,
            SessionMiddleware::class,
            TwigMiddleware::class,
            ExceptionHandlerMiddleware::class,
        ];
    }

    /**
     * Return an array of all registered Twig Extensions.
     *
     * @return \Twig\Extension\ExtensionInterface[]
     */
    public static function getTwigExtensions(): array
    {
        return [
            AssetsExtension::class,
            CoreExtension::class,
            // CsrfExtension::class,
            I18nExtension::class,
            AlertsExtension::class,
        ];
    }

    public static function getMigrations(): array
    {
        return [
            SessionsTable::class,
            ThrottlesTable::class,
        ];
    }

    /**
     * Return an array of all locator Resource Steams to register with locator.
     *
     * @return \UserFrosting\UniformResourceLocator\ResourceStreamInterface[]
     */
    public static function getResourceStreams(): array
    {
        return [
            // new ResourceStream('assets'),
            new ResourceStream('sprinkles', path: ''),
            new ResourceStream('config'),
            new ResourceStream('extra'),
            new ResourceStream('factories'), // TODO Change to DI
            new ResourceStream('locale'),
            new ResourceStream('schema'),
            new ResourceStream('templates'),

            new ResourceStream('cache', shared: true),
            new ResourceStream('logs', shared: true),
            new ResourceStream('sessions', shared: true),
            new ResourceStream('storage', shared: true),
        ];
    }
}
