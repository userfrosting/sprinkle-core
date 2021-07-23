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

/*
* Override Slim's default router with the UF router.
*
* @return \UserFrosting\Sprinkle\Core\Router
*/
class RouterService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO Reimplement. See https://stackoverflow.com/questions/59680412/slim-4-get-all-routes-into-a-controller-without-app
            // TODO Add interface
            'router' => function ($c) {
                $routerCacheFile = false;
                if (isset($c->config['settings.routerCacheFile'])) {
                    $filename = $c->config['settings.routerCacheFile'];
                    $routerCacheFile = $c->locator->findResource("cache://$filename", true, true);
                }

                return (new Router())->setCacheFile($routerCacheFile);
            },
        ];
    }
}