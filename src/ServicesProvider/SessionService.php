<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Illuminate\Database\Connection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\FileSessionHandler;
use Illuminate\Session\NullSessionHandler;
use Psr\Container\ContainerInterface;
use SessionHandlerInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Exceptions\BadConfigException;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * PHP session related services, link classes with the configuration.
 */
class SessionService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            /**
             * Inject config into Session.
             */
            Session::class => function (SessionHandlerInterface $handler, Config $config) {
                return new Session($handler, $config->get('session'));
            },

            /**
             * Select Handler based on Config.
             *
             * @throws BadConfigException
             */
            SessionHandlerInterface::class => function (ContainerInterface $ci, Config $config) {
                // Create appropriate handler based on config
                switch ($config->get('session.handler')) {
                    case 'file':
                        return $ci->get(FileSessionHandler::class);
                    break;
                    case 'database':
                        return $ci->get(DatabaseSessionHandler::class);
                    break;
                    case 'array':
                        return $ci->get(NullSessionHandler::class);
                    break;
                    default:
                        throw new BadConfigException("Bad session handler type '{$config->get('session.handler')}' specified in configuration file.");
                    break;
                }
            },

            /**
             * Inject dependencies into FileSessionHandler.
             */
            FileSessionHandler::class => function (Filesystem $fs, Config $config, ResourceLocatorInterface $locator) {
                return new FileSessionHandler($fs, $locator->findResource('session://'), $config->get('session.minutes'));
            },

            /**
             * Inject dependencies into DatabaseSessionHandler.
             * Table must exist, otherwise an exception will be thrown.
             */
            DatabaseSessionHandler::class => function (Connection $connection, Config $config) {
                return new DatabaseSessionHandler($connection, $config->get('session.database.table'), $config->get('session.minutes'));
            },
        ];
    }
}
