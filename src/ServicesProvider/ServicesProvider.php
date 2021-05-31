<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Psr\Container\ContainerInterface;
use UserFrosting\Sprinkle\Core\Util\CheckEnvironment;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

/**
 * UserFrosting core services provider.
 *
 * Registers core services for UserFrosting, such as config, database, asset manager, translator, etc.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 *
 * TODO ::: DELETE THIS FILE
 */
class ServicesProvider
{
    /**
     * Register UserFrosting's core services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and psr-container.
     */
    public function register(ContainerInterface $container)
    {
        /*
         * Middleware to check environment.
         *
         * @todo We should cache the results of this, the first time that it succeeds.
         *
         * @return \UserFrosting\Sprinkle\Core\Util\CheckEnvironment
         */
        // TODO : Replace this for something better
        $container['checkEnvironment'] = function ($c) {
            return new CheckEnvironment($c->view, $c->locator, $c->cache);
        };

        /*
         * Class mapper.
         *
         * Creates an abstraction on top of class names to allow extending them in sprinkles.
         *
         * @return \UserFrosting\Sprinkle\Core\Util\ClassMapper
         */
        // TODO : Irrelevant now as DI can take care of this.
        $container['classMapper'] = function ($c) {
            $classMapper = new ClassMapper();
            $classMapper->setClassMapping('query_builder', 'UserFrosting\Sprinkle\Core\Database\Builder');
            $classMapper->setClassMapping('eloquent_builder', 'UserFrosting\Sprinkle\Core\Database\EloquentBuilder');
            $classMapper->setClassMapping('throttle', 'UserFrosting\Sprinkle\Core\Database\Models\Throttle');

            return $classMapper;
        };
    }
}
