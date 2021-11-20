<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Routes;

use Slim\App;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Core\Controller\AlertsController;
use UserFrosting\Sprinkle\Core\Util\NoCache;

class AlertsRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/alerts', AlertsController::class); //->add(new NoCache()); // TODO
    }
}