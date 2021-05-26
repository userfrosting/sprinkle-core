<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2021 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use Slim\App;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Core\Controller\CoreController;
use UserFrosting\Sprinkle\Core\Util\NoCache;

class Routes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        // TODO : Update config definition
        // $config = $app->getContainer()->get('config');

        $app->get('/alerts', [CoreController::class, 'jsonAlerts'])->add(new NoCache());
        // $app->get('/' . $config['assets.raw.path'] . '/{url:.+}', [CoreController::class, 'getAsset']);
        $app->get('/assets-raw/{url:.+}', [CoreController::class, 'getAsset']);
    }
}
