<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationDependencyNotMetException;

/**
 * Helper class used to analyse migrations dependencies and return the
 * migrations classes in the correct order for migration to be run up without
 * dependency collisions.
 */
class MigrationDependencyAnalyser
{
    /**
     * @param MigrationRepositoryInterface $repository
     * @param MigrationLocatorInterface    $locator
     */
    public function __construct(
        protected MigrationRepositoryInterface $repository,
        protected MigrationLocatorInterface $locator,
    ) {
    }

    /**
     * Get installed migrations
     *
     * @return string[] An array of migration class names in the order they where ran.
     */
    public function getInstalled(): array
    {
        return $this->repository->getMigrationsList();
    }

    /**
     * Get migrations available and registered to be run.
     *
     * @return string[] An array of migration class names.
     */
    public function getAvailable(): array
    {
        return $this->locator->list();
    }

    /**
     * Returns migration that are installed, but not available.
     *
     * @return string[] An array of migration class names.
     */
    public function getStale(): array
    {
        $stale = array_diff($this->getInstalled(), $this->getAvailable());

        return array_values($stale);
    }

    /**
     * Returns migration that are available, but not installed, in the order
     * they need to be run.
     *
     * @return string[] An array of migration class names.
     */
    public function getPending(): array
    {
        $pending = array_diff($this->getAvailable(), $this->getInstalled());

        $migrations = [];

        // We need to validate the dependencies of each pending migration.
        foreach ($pending as $migration) {
            $dependencies = $this->getPendingDependencies($migration);
            $migrations = array_merge($migrations, $dependencies);
        }

        // Remove duplicate and re-index.
        // Duplicate migrations will be removed, preserving the correct order.
        $migrations = array_unique($migrations);
        $migrations = array_values($migrations);

        return $migrations;
    }

    /**
     * We run across all step even if it's not required, as this is run
     * recursively across all dependencies.
     *
     * @param string $migrationClass
     *
     * @return string[] This migration and it's dependencies.
     */
    protected function getPendingDependencies(string $migrationClass): array
    {
        // Get migration instance
        $migration = $this->locator->get($migrationClass);

        // Start with the migration in the return.
        // This allows the main migration to be placed in the correct order
        // When doing a nested call.
        $dependencies = [$migrationClass];

        // Loop dependencies and validate them
        foreach ($this->getDependenciesProperty($migration) as $dependency) {

            // The dependency might already be installed. Check that first.
            // Skip if it's the case (we don't want it pending again).
            // This allows to accept stale (installed, but not available
            // migration) as valid dependencies.
            if (in_array($dependency, $this->getInstalled())) {
                continue;
            }

            // Make sure dependency exist. Otherwise it's a dead end.
            if (!$this->locator->has($dependency)) {
                throw new MigrationDependencyNotMetException("$migrationClass depends on $dependency, but it's not available.");
            }

            // Loop dependency's dependencies. Add them BEFORE the main migration and previous dependencies.
            $dependencies = array_merge($this->getPendingDependencies($dependency), $dependencies);
        }

        return $dependencies;
    }

    /**
     * Returns the migration dependency list.
     *
     * @param MigrationInterface $migration The migration instance
     *
     * @return string[] The dependency list
     */
    protected function getDependenciesProperty(MigrationInterface $migration): array
    {
        // Should be handled by interface, but since it a property, it's not... so should still be kept in case.
        // If the `dependencies` property exist, use it
        if (property_exists($migration, 'dependencies')) {
            return $migration::$dependencies;
        } else {
            return [];
        }
    }
}
