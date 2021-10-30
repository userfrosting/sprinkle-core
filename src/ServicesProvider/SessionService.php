<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\FileSessionHandler;
use Illuminate\Session\NullSessionHandler;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Session\Session;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/*
 * Start the PHP session, with the name and parameters specified in the configuration file.
 *
 * @throws \Exception
 */
class SessionService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO : Custom exception should be used.
            // TODO : Container could be used to instantiate *AlertStream and limit the number of dependencies required (but would depend on the whole container... PHP-DI docs should be consulted to find the best way to do this).
            //        -> *AlertStream can be defined down here, and instead of returning "new...", it return "ci->get(...)"
            Session::class => function (Capsule $db, Config $config, ResourceLocatorInterface $locator) {

                // Create appropriate handler based on config
                switch ($config->get('session.handler')) {
                    case 'file':
                        $fs = new Filesystem(); // TODO : Should be injected
                        $handler = new FileSessionHandler($fs, $locator->findResource('session://'), $config->get('session.minutes'));
                    break;
                    case 'database':
                        $connection = $db->connection();
                        // Table must exist, otherwise an exception will be thrown
                        $handler = new DatabaseSessionHandler($connection, $config->get('session.database.table'), $config->get('session.minutes'));
                    break;
                    case 'array':
                        $handler = new NullSessionHandler();
                    break;
                    default:
                        throw new \Exception("Bad session handler type '{$config['session.handler']}' specified in configuration file.");
                    break;
                }

                // Create and return a new wrapper for $_SESSION
                $session = new Session($handler, $config->get('session'));

                return $session;
            },
        ];
    }
}
