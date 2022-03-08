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
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Schema\Builder;
use Illuminate\Events\Dispatcher;
use UserFrosting\Config\Config;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Listeners\LogExecutedQuery;

/*
 * Initialize Eloquent Capsule, which provides the database layer for UF.
 *
 * @todo construct the individual objects rather than using the facade
 * @return \Illuminate\Database\Capsule\Manager
*/
class DatabaseService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            Capsule::class => function (Config $config, LogExecutedQuery $logger) {
                $capsule = new Capsule();

                // Add each defined connection in the config
                foreach ($config->get('db.connections') as $name => $dbConfig) {
                    $capsule->addConnection($dbConfig, $name);
                }

                // Set default connection
                $defaultConnection = $config->get('db.default');
                $capsule->getDatabaseManager()->setDefaultConnection($defaultConnection);

                // Set Event Dispatcher
                $queryEventDispatcher = new Dispatcher();
                $capsule->setEventDispatcher($queryEventDispatcher);

                // Register as global connection
                $capsule->setAsGlobal();

                // Start Eloquent
                $capsule->bootEloquent();

                // Listen to QueryExecuted event and send debug to logger if required by config
                if ($config->get('debug.queries')) {
                    $queryEventDispatcher->listen(QueryExecuted::class, $logger);
                }

                return $capsule;
            },

            Builder::class => function (Connection $connection) {
                return $connection->getSchemaBuilder();
            },

            Connection::class => function (Capsule $db) {
                $connection = $db->getDatabaseManager()->getDefaultConnection();

                return $db->getConnection($connection);
            },
        ];
    }
}
