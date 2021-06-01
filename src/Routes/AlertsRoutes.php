<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
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
