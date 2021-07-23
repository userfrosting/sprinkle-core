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

/**
 * Migration Locator Class.
 *
 * Finds all migrations class in a given sprinkle
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
    public function getMigrations(): array
    {
        $migrations = $this->extensionLoader->getInstances(
            method: 'getMigrations',
            recipeInterface: MigrationRecipe::class,
            extensionInterface: MigrationInterface::class,
        );

        return $migrations;
    }
}
