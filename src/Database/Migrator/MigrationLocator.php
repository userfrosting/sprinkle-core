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
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\RecipeExtensionLoader;
use UserFrosting\Support\Exception\NotFoundException;

/**
 * Find and returns all registered MigrationInterface across all sprinkles, using MigrationRecipe.
 */
class MigrationLocator implements MigrationLocatorInterface
{
    /**
     * @param RecipeExtensionLoader $extensionLoader
     */
    public function __construct(protected RecipeExtensionLoader $extensionLoader)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        $migrations = $this->extensionLoader->getInstances(
            method: 'getMigrations',
            recipeInterface: MigrationRecipe::class,
            extensionInterface: MigrationInterface::class,
        );

        return $migrations;
    }

    /**
     * {@inheritDoc}
     */
    public function list(): array
    {
        return array_map(function ($m) {
            return get_class($m);
        }, $this->all());
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $migration): MigrationInterface
    {
        if (!$this->has($migration)) {
            throw new NotFoundException("Migration `$migration` not found.");
        }

        $results = array_filter($this->all(), function ($m) use ($migration) {
            return get_class($m) == $migration;
        });

        return array_values($results)[0]; // TODO : Test array_values with filter not being on key 0
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $migration): bool
    {
        $list = $this->list();

        return in_array($migration, $list);
    }
}
