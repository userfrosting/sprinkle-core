<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Csrf;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

/**
 * CSRF Provider interface.
 *
 * CSRF Providers
 */
interface CsrfProviderInterface
{
    /**
     * Setup the CSRF service.
     * Returns the CSRF Guard which will be added to the app later.
     *
     * @param ContainerInterface $ci
     *
     * @return mixed The csrf guard
     */
    public static function setupService(ContainerInterface $ci);

    /**
     * Register middleware.
     * Add the guard to the app as a middleware.
     *
     * @param App     $app
     * @param Request $request
     * @param mixed   $guard
     */
    public static function registerMiddleware(App $app, Request $request, $guard);
}
