<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use UserFrosting\Alert\AlertStream;

/**
 * AlertController Class.
 *
 * Implements routes to get all alerts messages.
 */
class AlertsController
{
    /**
     * Render the alert stream as a JSON object.
     * The alert stream contains messages which have been generated by calls to `MessageStream::addMessage` and `MessageStream::addMessageTranslated`.
     * Request type: GET.
     *
     * @param Response $response
     */
    public function __invoke(Response $response, AlertStream $alerts): Response
    {
        $payload = json_encode($alerts->getAndClearMessages(), JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
