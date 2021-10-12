<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Middleware to add a 'Cache-Control' header to the response to prevent caching.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class NoCache
{
    /**
     * Invoke the NoCache middleware, adding headers to the response to prevent caching.
     *
     * @param Request  $request  PSR7 request
     * @param Response $response PSR7 response
     * @param callable $next     Next middleware
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $response = $response->withHeader('Cache-Control', 'no-store');

        return $next($request, $response);
    }
}
