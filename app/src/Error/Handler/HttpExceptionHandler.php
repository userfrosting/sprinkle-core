<?php


declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Throwable;

/**
 * Custom handler for all HttpException.
 */
final class HttpExceptionHandler extends ExceptionHandler
{
    /**
     * Never display error details for HttpException.
     *
     * @return bool
     */
    protected function displayErrorDetails(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function determineStatusCode(ServerRequestInterface $request, Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getCode();
        }

        return parent::determineStatusCode($request, $exception);
    }
}
