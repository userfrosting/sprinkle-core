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
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Database\Migrator\SprinkleMigrationLocator;
use UserFrosting\Support\Repository\Repository as Config;

/*
 * Migrator service.
 *
 * This service handles database migration operations
 *
 * @return \UserFrosting\Sprinkle\Core\Database\Migrator\Migrator
*/
class MigratorService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // Migrator::class => function (
            //     Capsule $db,
            //     MigrationRepositoryInterface $migrationRepository,
            //     MigrationLocatorInterface $migrationLocator
            // ) {
            //     $migrator = new Migrator(
            //         $db,
            //         $migrationRepository,
            //         $migrationLocator,
            //     );

            //     return $migrator;
            // },

            // TODO : Should probably depend on a service, not a string ?
            MigrationRepositoryInterface::class => function (Capsule $db, Config $config) {
                $repository = new DatabaseMigrationRepository($db, $config->get('migrations.repository_table'));

                return $repository;
            },

            MigrationLocatorInterface::class => \DI\autowire(SprinkleMigrationLocator::class),
        ];
    }
}
