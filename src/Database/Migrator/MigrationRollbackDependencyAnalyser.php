<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use UserFrosting\Sprinkle\Core\Exceptions\MigrationRollbackException;

/**
 * Helper class used to analyse migrations rollback dependencies and return the
 * list of migrations classes that prevent the specified migrations to be rollback
 */
class MigrationRollbackDependencyAnalyser extends MigrationDependencyAnalyser
{
    /**
     * Returns the migrations to be rollback.
     *
     * MigrationRollbackException will be thrown if something prevent the
     * migrations from being executed. If the array is returned, everything is
     * fine to proceed.
     *
     * @param int $steps Number of steps to rollback. Default to 1 (last ran migration)
     *
     * @return string[] An array of migration class to be rolled down.
     */
    public function getMigrationsForRollback(int $steps = 1): array
    {
        $migrations = $this->repository->list(steps: $steps, asc: false);

        $this->checkRollbackDependencies($migrations);

        return $migrations;
    }

    /**
     * Returns the migrations to be reset (all of them).
     *
     * MigrationRollbackException will be thrown if something prevent the
     * migrations from being executed. If the array is returned, everything is
     * fine to proceed.
     *
     * @return string[] An array of migration class to be rolled down.
     */
    public function getMigrationsForReset(): array
    {
        $migrations = $this->repository->list(asc: false);

        $this->checkRollbackDependencies($migrations);

        return $migrations;
    }

    /**
     * Return if the specified migration class can be rollback or not.
     *
     * @param string $migration
     *
     * @return bool True if can rollback, false if not.
     */
    public function canRollbackMigration(string $migration): bool
    {
        try {
            $this->validateRollbackMigration($migration);
        } catch (MigrationRollbackException $e) {
            return false;
        }

        return true;
    }

    /**
     * Test if a migration can be rollback.
     *
     * @param  string                     $migration
     * @param  string[]                   $installed
     * @throws MigrationRollbackException If something prevent migration to be rollback
     */
    public function validateRollbackMigration(string $migration, ?array $installed = null): void
    {
        // Can't rollback if migration is not installed
        if (!$this->repository->has($migration)) {
            throw new MigrationRollbackException('Migration is not installed : ' . $migration);
        }

        // Can't rollback anything if there's any stale migration
        if (!empty($stale = $this->getStale())) {
            throw new MigrationRollbackException('Stale migration detected : ' . implode(', ', $stale));
        }

        // If no installed, use the whole stack of installed
        if ($installed === null) {
            $installed = $this->getInstalled();
        }

        // We need to validate the dependencies of each installed migration
        // To make sure any of them doesn't depends on the one we wan to rollback.
        foreach ($installed as $installedMigration) {

            // Get all dependencies for $installed, make sure $migrations is not in them
            $dependencies = $this->getInstalledDependencies($installedMigration);
            if (in_array($migration, $dependencies)) {
                throw new MigrationRollbackException("$migration cannot be rolled back since $installedMigration depends on it.");
            }
        }
    }

    /**
     * We run across all step even if it's not required, as this is run
     * recursively across all dependencies.
     *
     * @param string $migrationClass
     *
     * @return string[] This migration and it's dependencies.
     */
    protected function getInstalledDependencies(string $migrationClass): array
    {
        // Get migration instance
        $migration = $this->locator->get($migrationClass);

        // Return variable
        $dependencies = [];

        // Loop dependencies and validate them
        foreach ($this->getDependenciesProperty($migration) as $dependency) {

            // Make sure dependency exist. Otherwise it's a dead end.
            if (!$this->locator->has($dependency)) {
                throw new MigrationRollbackException("$migrationClass depends on $dependency, but it's not available.");
            }

            // Loop dependency's dependencies. Add them BEFORE the main migration and previous dependencies.
            $dependencies = array_merge($this->getPendingDependencies($dependency), $dependencies);
        }

        return $dependencies;
    }

    /**
     * Loop all migrations and test each one for rollback, recursively passing
     * the remaining migrations, simulating the removal of each migration as
     * each loop is parsed.
     *
     * N.B.: The `installed` migration stack are NOT passed to `testRollbackMigration`
     * here, only the array of migrations passed as argument. By design, all
     * migrations received, in this class, will be in reverse order they have been
     * run. It works because it's theoretically impossible for an older batch to
     * depend on a newer batch. The only way would be for a definition to have
     * changed. To avoid this, both install should cloned, then remove from this,
     * but the current solution is more optimized.
     *
     * @param string[] $migrations
     */
    protected function checkRollbackDependencies(array $migrations): void
    {
        foreach ($migrations as $migration) {

            // Exception will be thrown if can't rollback.
            $this->validateRollbackMigration($migration, $migrations);

            // Remove the migration form the list, to simulate it's been
            // rollback for the next pass.
            $migrations = array_filter($migrations, fn ($m) => $m != $migration);
        }
    }
}
