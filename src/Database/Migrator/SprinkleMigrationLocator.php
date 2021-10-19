<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\Core\Util\ClassRepository\AbstractClassRepository;
use UserFrosting\Sprinkle\RecipeExtensionLoader;

/**
 * Find and returns all migrations definitions (classes) registered and available.
 */
class SprinkleMigrationLocator extends AbstractClassRepository implements MigrationLocatorInterface
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
        return $this->extensionLoader->getInstances(
            method: 'getMigrations',
            recipeInterface: MigrationRecipe::class,
            extensionInterface: MigrationInterface::class,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $migration): MigrationInterface
    {
        // Wrap around parent to satisfy interface
        return parent::get($migration);
    }
}
