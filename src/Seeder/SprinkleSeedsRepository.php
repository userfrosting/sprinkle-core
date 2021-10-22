<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Seeder;

use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe;
use UserFrosting\Sprinkle\Core\Util\ClassRepository\AbstractClassRepository;
use UserFrosting\Sprinkle\RecipeExtensionLoader;

/**
 * Find and returns all registered SeedInterface across all sprinkles, using SeedRecipe.
 */
class SprinkleSeedsRepository extends AbstractClassRepository implements SeedRepositoryInterface
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
            method: 'getSeeds',
            recipeInterface: SeedRecipe::class,
            extensionInterface: SeedInterface::class,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $migration): SeedInterface
    {
        // Wrap around parent to satisfy interface
        return parent::get($migration);
    }
}
