<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use UserFrosting\Assets\AssetLoader;

/**
 * AssetsController Class.
 *
 * Implements assets related route.
 */
class AssetsController
{
    /**
     * Handle all requests for raw assets.
     * Request type: GET.
     *
     * @param string   $url
     * @param Response $response
     */
    public function __invoke($url, Response $response, AssetLoader $assetLoader): Response
    {
        if (!isset($url) || !$assetLoader->loadAsset($url)) {
            return $response->withStatus(404);
        }

        $response->getBody()->write($assetLoader->getContent());

        return $response
            ->withHeader('Content-Type', $assetLoader->getType())
            ->withHeader('Content-Length', $assetLoader->getLength());
    }
}
