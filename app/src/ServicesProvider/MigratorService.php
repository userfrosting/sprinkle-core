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
use UserFrosting\Sprinkle\Core\Database\Migrator\SprinkleMigrationLocator;
use UserFrosting\Support\Repository\Repository as Config;

/*
 * Migrator service.
 *
 * This service handles database migration operations.
*/
class MigratorService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            MigrationRepositoryInterface::class => function (Capsule $db, Config $config) {
                return new DatabaseMigrationRepository($db, $config->get('migrations.repository_table'));
            },

            MigrationLocatorInterface::class => \DI\autowire(SprinkleMigrationLocator::class),
        ];
    }
}