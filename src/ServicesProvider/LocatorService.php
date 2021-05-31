<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceLocator;

class LocatorService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            ResourceLocatorInterface::class => function (SprinkleManager $sprinkleManager) {

                // Create instance based on main sprinkle path
                $mainSprinkle = $sprinkleManager->getMainSprinkle();
                $locator = new ResourceLocator($mainSprinkle::getPath());

                // Register all sprinkles locations
                foreach ($sprinkleManager->getSprinkles() as $sprinkle) {
                    $locator->registerLocation($sprinkle::getName(), $sprinkle::getPath());
                }

                // Register core locator shared streams
                $locator->registerStream('cache', '', \UserFrosting\CACHE_DIR_NAME, true);
                $locator->registerStream('log', '', \UserFrosting\LOG_DIR_NAME, true);
                $locator->registerStream('session', '', \UserFrosting\SESSION_DIR_NAME, true);

                // Register core locator sprinkle streams
                $locator->registerStream('sprinkles', '', '');
                $locator->registerStream('config');
                $locator->registerStream('extra', '', \UserFrosting\EXTRA_DIR_NAME);
                $locator->registerStream('factories', '', \UserFrosting\FACTORY_DIR_NAME);
                $locator->registerStream('locale', '', \UserFrosting\LOCALE_DIR_NAME);
                $locator->registerStream('routes', '', \UserFrosting\ROUTE_DIR_NAME);
                $locator->registerStream('schema', '', \UserFrosting\SCHEMA_DIR_NAME);
                $locator->registerStream('templates', '', \UserFrosting\TEMPLATE_DIR_NAME);

                // Register core sprinkle class streams
                $locator->registerStream('seeds', '', \UserFrosting\SEEDS_DIR);
                $locator->registerStream('migrations', '', \UserFrosting\MIGRATIONS_DIR);

                return $locator;
            },
        ];
    }
}
