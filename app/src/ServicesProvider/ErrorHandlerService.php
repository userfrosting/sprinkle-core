<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Slim\Exception\HttpNotFoundException;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerMiddleware;
use UserFrosting\Sprinkle\Core\Error\Handler\HttpNotFoundExceptionHandler;
use UserFrosting\Sprinkle\Core\Error\Handler\NotFoundExceptionHandler;
use UserFrosting\Sprinkle\Core\Error\Handler\PhpMailerExceptionHandler;

class ErrorHandlerService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            /**
             * Register custom Handler
             */
            ExceptionHandlerMiddleware::class => \DI\decorate(function (ExceptionHandlerMiddleware $middleware) {

                // Register the NotFoundExceptionHandler.
                $middleware->registerHandler(HttpNotFoundException::class, HttpNotFoundExceptionHandler::class);

                // Register the PhpMailerExceptionHandler.
                // $middleware->registerHandler('\phpmailerException', PhpMailerExceptionHandler::class);

                return $middleware;
            })
        ];
    }
}
