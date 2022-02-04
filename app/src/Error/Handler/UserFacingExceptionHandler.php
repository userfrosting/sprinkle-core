<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Generic handler for exceptions that will be presented to the end user.
 * Override the default behavior and status code.
 */
final class UserFacingExceptionHandler extends ExceptionHandler
{
    /**
     * Don't log theses exceptions.
     */
    protected function shouldLogExceptions(): bool
    {
        return false;
    }

    /**
     * Don't display details for theses exceptions.
     */
    protected function displayErrorDetails(): bool
    {
        return false;
    }

    /**
     * Force the use if Exception code for AuthException.
     */
    protected function determineStatusCode(ServerRequestInterface $request, Throwable $exception): int
    {
        return intval($exception->getCode());
    }
}
