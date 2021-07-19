<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/*
 * Factory service with FactoryMuffin.
 *
 * Provide access to factories for the rapid creation of objects for the purpose of testing
 *
 * @return \League\FactoryMuffin\FactoryMuffin
 */
class FactoryService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO See if there's a better way to use this, as there's no point for the CI to be loaded for Unit (mock) Test
            FactoryMuffin::class => function (ResourceLocatorInterface $locator) {

                // Get the path of all of the sprinkle's factories
                $factoriesPath = $locator->findResources('factories://', true);

                // Create a new Factory Muffin instance
                $fm = new FactoryMuffin();

                // Load all of the model definitions
                $fm->loadFactories($factoriesPath);

                // Set the locale. Could be the config one, but for testing English should do
                Faker::setLocale('en_EN');

                return $fm;
            },
        ];
    }
}
