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
 * Handler for ValidationException. Override the default behavior and status code.
 */
final class ValidationExceptionHandler extends ExceptionHandler
{
    /**
     * Never log exceptions for ValidationException.
     */
    protected function shouldLogExceptions(): bool
    {
        return false;
    }

    /**
     * Never display error details for ValidationException.
     */
    protected function displayErrorDetails(): bool
    {
        return false;
    }

    /**
     * Force 400 error code for ValidationException.
     */
    protected function determineStatusCode(ServerRequestInterface $request, Throwable $exception): int
    {
        return 400;
    }
}
