<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use Slim\App;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Core\Controller\AlertsController;
use UserFrosting\Sprinkle\Core\Controller\AssetsController;
use UserFrosting\Sprinkle\Core\Util\NoCache;
use UserFrosting\Support\Repository\Repository as Config;

class Routes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $config = $app->getContainer()->get(Config::class);

        $app->get('/alerts', AlertsController::class); //->add(new NoCache()); // TODO
        $app->get('/' . $config['assets.raw.path'] . '/{url:.+}', AssetsController::class);
    }
}
