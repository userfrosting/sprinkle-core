<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Csrf;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Exception\HttpBadRequestException;
use UserFrosting\Session\Session;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Slim Csrf Provider Class.
 */
// TODO : This class still need a rewriting !
class SlimCsrfProvider implements CsrfProviderInterface
{
    public function __construct(
        protected Config $config,
        protected Session $session,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return \Slim\Csrf\Guard
     */
    public static function setupService()
    {
        $csrfKey = $this->config->get('session.keys.csrf');

        // Workaround so that we can pass storage into CSRF guard.
        // If we tried to directly pass the indexed portion of `session` (for example, $ci->session['site.csrf']),
        // we would get an 'Indirect modification of overloaded element of UserFrosting\Session\Session' error.
        // If we tried to assign an array and use that, PHP would only modify the local variable, and not the session.
        // Since ArrayObject is an object, PHP will modify the object itself, allowing it to persist in the session.
        if (!$this->session->has($csrfKey)) {
            $this->session[$csrfKey] = new \ArrayObject();
        }
        $csrfStorage = $this->session[$csrfKey];

        $onFailure = function ($request, $response, $next) {
            // TODO : This will NOT WORK. HttpBadRequestException requires the request.
            // BadRequestException was removed, a new custom exception might be needed
            // (but this whole class requires a rewrite)
            $e = new HttpBadRequestException('The CSRF code was invalid or not provided.');
            $e->addUserMessage('CSRF_MISSING');

            throw $e;
        };

        return new Guard(
            $this->config->get('csrf.name'),
            $csrfStorage,
            $onFailure,
            $this->config->get('csrf.storage_limit'),
            $this->config->get('csrf.strength'),
            $this->config->get('csrf.persistent_token')
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function registerMiddleware(App $app, Request $request, $guard)
    {
        // Global on/off switch
        if (!$this->config->get('csrf.enabled')) {
            return;
        }

        $path = $request->getUri()->getPath();
        $method = ($request->getMethod()) ?: 'GET';

        // Normalize path to always have a leading slash
        $path = '/' . ltrim($path, '/');

        // Normalize method to uppercase.
        $method = strtoupper($method);

        $csrfBlacklist = $this->config->get('csrf.blacklist');
        $isBlacklisted = false;

        // Go through the blacklist and determine if the path and method match any of the blacklist entries.
        foreach ($csrfBlacklist as $pattern => $methods) {
            $methods = array_map('strtoupper', (array) $methods);
            if (in_array($method, $methods) && $pattern != '' && preg_match('~' . $pattern . '~', $path)) {
                $isBlacklisted = true;
                break;
            }
        }

        if (!$path || !$isBlacklisted) {
            $app->add($guard);
        }
    }
}
