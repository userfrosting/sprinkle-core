<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use RocketTheme\Toolbox\Event\Event;
use UserFrosting\Sprinkle\Core\Csrf\SlimCsrfProvider;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\System\Sprinkle\Sprinkle;

/**
 * Bootstrapper class for the core sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CoreOld extends Sprinkle
{
    /**
     * Defines which events in the UF lifecycle our Sprinkle should hook into.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onSprinklesInitialized'      => ['onSprinklesInitialized', 0],
            'onAddGlobalMiddleware'       => ['onAddGlobalMiddleware', 0],
        ];
    }

    /**
     * Set static references to DI container in necessary classes.
     */
    public function onSprinklesInitialized()
    {
        // Set container for data model
        // Model::$ci = $this->ci;
    }

    /**
     * Add CSRF middleware.
     *
     * @param Event $event
     */
    public function onAddGlobalMiddleware(Event $event)
    {
        // Don't register CSRF if CLI
        if (!$this->ci->cli) {
            SlimCsrfProvider::registerMiddleware($event->getApp(), $this->ci->request, $this->ci->csrf);
        }
    }
}
