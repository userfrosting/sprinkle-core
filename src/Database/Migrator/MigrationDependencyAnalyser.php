<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use UserFrosting\Sprinkle\Core\Util\BadClassNameException;

/**
 * Helper class used to analyse migrations dependencies and return the
 * migrations classes in the correct order for migration to be run up without
 * dependency collisions.
 */
class MigrationDependencyAnalyser
{
    /**
     * @var \Illuminate\Support\Collection List of fulfillable migrations
     */
    protected $fulfillable;

    /**
     * @var \Illuminate\Support\Collection List of unfulfillable migration (Migration that needs to be run and their dependencies are NOT met)
     */
    protected $unfulfillable;

    /**
     * @var \Illuminate\Support\Collection List of installed migration
     */
    protected $installed;

    /**
     * @var \Illuminate\Support\Collection List of migration to install
     */
    protected $pending;

    /**
     * @var bool True/false if the analyse method has been called
     */
    protected $analysed = false;

    /**
     * @param array $pending   The pending migrations
     * @param array $installed The installed migrations
     */
    // TODO Move those to setter/getter, or move analyse to ctor ?
    // TODO : Depends on Localiser & Repo
    public function __construct(array $pending = [], array $installed = [])
    {
        $this->pending = collect($this->normalizeClasses($pending));
        $this->installed = collect($this->normalizeClasses($installed));
    }

    /**
     * Analyse the dependencies.
     */
    // TODO : No bueno anymore
    public function analyse(): void
    {
        // Reset fulfillable/unfulfillable lists
        $this->analysed = false;
        $this->fulfillable = collect([]);
        $this->unfulfillable = collect([]);

        // Loop pending and check for dependencies
        foreach ($this->pending as $migration) {
            $this->validateClassDependencies($migration);
        }

        $this->analysed = true;
    }

    /**
     * Validate if a migration is fulfillable.
     * N.B.: The key element here is the recursion while validating the
     * dependencies. This is very important as the order the migrations needs
     * to be run is defined by this recursion. By waiting for the dependency
     * to be marked as fulfillable to mark the parent as fulfillable, the
     * parent class will be automatically placed after it's dependencies
     * in the `fulfillable` property.
     *
     * @param string $migrationName The migration class name
     *
     * @return bool True/False if the migration is fulfillable
     */
    // TODO : Building a tree just like the sprinkle, then (or in the process of) remove installed might be simpler ?
    protected function validateClassDependencies(string $migrationName): bool
    {
        // If it's already marked as fulfillable, it's fulfillable
        // Return true directly (it's already marked)
        if ($this->fulfillable->contains($migrationName)) {
            return true;
        }

        // If it's already marked as unfulfillable, it's unfulfillable
        // Return false directly (it's already marked)
        if ($this->unfulfillable->contains($migrationName)) {
            return false;
        }

        // If it's already run, it's fulfillable
        // Mark it as such for next time it comes up in this point
        if ($this->installed->contains($migrationName)) {
            return $this->markAsFulfillable($migrationName);
        }

        // Get migration dependencies
        $dependencies = $this->getMigrationDependencies($migrationName);

        // Loop dependencies. If one is not fulfillable, then this migration is not either
        foreach ($dependencies as $dependency) {

            // The dependency might already be installed. Check that first
            if ($this->installed->contains($dependency)) {
                continue;
            }

            // Check is the dependency is pending installation. If so, check for it's dependencies.
            // If the dependency is not fulfillable, then this one isn't either
            if (!$this->pending->contains($dependency) || !$this->validateClassDependencies($dependency)) {
                return $this->markAsUnfulfillable($migrationName, $dependency);
            }
        }

        // If no dependencies returned false, it's fulfillable
        return $this->markAsFulfillable($migrationName);
    }

    /**
     * Return the fulfillable list. Analyse the stack if not done already.
     *
     * @return array
     */
    public function getFulfillable(): array
    {
        if (!$this->analysed) {
            $this->analyse();
        }

        return $this->fulfillable->toArray();
    }

    /**
     * Return the fulfillable list. Analyse the stack if not done already.
     *
     * @return array
     */
    public function getUnfulfillable(): array
    {
        if (!$this->analysed) {
            $this->analyse();
        }

        return $this->unfulfillable->toArray();
    }

    /**
     * Mark a dependency as fulfillable. Removes it from the pending list and add it to the fulfillable list.
     *
     * @param string $migration The migration class name
     *
     * @return bool True, it's fulfillable
     */
    protected function markAsFulfillable(string $migration): bool
    {
        $this->fulfillable->push($migration);

        return true;
    }

    /**
     * Mark a dependency as unfulfillable. Removes it from the pending list and add it to the unfulfillable list.
     *
     * @param string       $migration  The migration class name
     * @param string|array $dependency The problematic dependency
     *
     * @return bool False, it's not fulfillable
     */
    protected function markAsUnfulfillable(string $migration, $dependency): bool
    {
        if (is_array($dependency)) {
            $dependency = implode(', ', $dependency);
        }

        $this->unfulfillable->put($migration, $dependency);

        return false;
    }

    /**
     * Returns the migration dependency list.
     *
     * @param string $migration The migration class
     *
     * @return array The dependency list
     */
    // TODO : Might be worth splitting this outside. Aka, type hint on MigrationInterfaces instead of random Strings ?
    protected function getMigrationDependencies(string $migration): array
    {
        // Make sure class exists
        // TODO Use Locator `has` instead. It needs to be registered, not any class
        if (!class_exists($migration)) {
            // TODO : The command reference in the message should be moved in a try/catch in the Bakery command
            throw new BadClassNameException("Unable to find the migration class '$migration'. Run 'php bakery migrate:clean' to remove stale migrations.");
        }

        // TODO : Should be handled by interface, but since it a property, might  not... so should still be kept in case.
        // If the `dependencies` property exist, use it
        if (property_exists($migration, 'dependencies')) {
            return $this->normalizeClasses($migration::$dependencies);
        } else {
            return [];
        }
    }

    /**
     * Normalize class so all class starts with '/'.
     *
     * @param string[] $classes
     *
     * @return string[]
     */
    protected function normalizeClasses(array $classes): array
    {
        return array_map(function (string $class) {
            if ($class[0] !== '\\') {
                return '\\' . $class;
            }

            return $class;
        }, $classes);
    }
}
