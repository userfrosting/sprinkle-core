<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocator;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

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
            Migrator::class => function (Capsule $db, Config $config, ResourceLocatorInterface $locator) {
                $migrator = new Migrator(
                    $db,
                    new DatabaseMigrationRepository($db, $config['migrations.repository_table']),
                    new MigrationLocator($locator)
                );

                // Make sure repository exist
                if (!$migrator->repositoryExists()) {
                    $migrator->getRepository()->createRepository();
                }

                return $migrator;
            },
        ];
    }
}
