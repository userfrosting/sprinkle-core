<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\LocatorRecipe;
use UserFrosting\Sprinkle\RecipeExtensionLoader;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStreamInterface;

class LocatorService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            ResourceLocatorInterface::class => function (SprinkleManager $sprinkleManager, RecipeExtensionLoader $extensionLoader) {

                // Create instance based on main sprinkle path
                $mainSprinkle = $sprinkleManager->getMainSprinkle();
                $locator = new ResourceLocator($mainSprinkle::getPath());

                // Register all sprinkles locations
                foreach ($sprinkleManager->getSprinkles() as $sprinkle) {
                    $locator->registerLocation($sprinkle::getName(), $sprinkle::getPath());
                }

                // Register all sprinkles streams from recipes
                $streams = $extensionLoader->getObjects(
                    method: 'getResourceStreams',
                    recipeInterface: LocatorRecipe::class,
                    extensionInterface: ResourceStreamInterface::class,
                );

                foreach ($streams as $stream) {
                    $locator->addStream($stream);
                }

                return $locator;
            },
        ];
    }
}
