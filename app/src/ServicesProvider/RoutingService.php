<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Slim\App;
use Slim\Interfaces\RouteParserInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;

/*
* Slim's routing related services with the UF router.
*/
final class RoutingService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            /**
             * Alias for Router Parser in CI for easier access.
             *
             * @see https://www.slimframework.com/docs/v4/objects/routing.html#route-names
             */
            RouteParserInterface::class => function (App $app) {
                return $app->getRouteCollector()->getRouteParser();
            },
        ];
    }
}
