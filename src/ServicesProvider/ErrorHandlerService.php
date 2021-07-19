<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerManager;
use UserFrosting\Sprinkle\Core\Error\Handler\HttpExceptionHandler;
use UserFrosting\Sprinkle\Core\Error\Handler\NotFoundExceptionHandler;
use UserFrosting\Sprinkle\Core\Error\Handler\PhpMailerExceptionHandler;
use UserFrosting\Support\Exception\HttpException;
use UserFrosting\Support\Exception\NotFoundException;

class ErrorHandlerService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            /*
            * Custom error-handler for recoverable errors.
            *
            * @return \UserFrosting\Sprinkle\Core\Error\ExceptionHandlerManager
            */
            // TODO Add interface, rework injections, replace settings
            ExceptionHandlerManager::class => function ($c) {
                $settings = $c->settings;

                $handler = new ExceptionHandlerManager($c, $settings['displayErrorDetails']);

                // Register the base HttpExceptionHandler.
                $handler->registerHandler(HttpException::class, HttpExceptionHandler::class);

                // Register the NotFoundExceptionHandler.
                $handler->registerHandler(NotFoundException::class, NotFoundExceptionHandler::class);

                // Register the PhpMailerExceptionHandler.
                $handler->registerHandler('\phpmailerException', PhpMailerExceptionHandler::class);

                return $handler;
            },

            /*
            * Error-handler for 404 errors.  Notice that we manually create a UserFrosting NotFoundException,
            * and a NotFoundExceptionHandler.  This lets us pass through to the UF error handling system.
            *
            * @return callable
            */
            // TODO Implement properly
            'notFoundHandler' => function ($c) {
                return function ($request, $response) use ($c) {
                    $exception = new NotFoundException();
                    $handler = new NotFoundExceptionHandler($c, $request, $response, $exception, $c->settings['displayErrorDetails']);

                    return $handler->handle();
                };
            },

            /*
            * Error-handler for PHP runtime errors.  Notice that we just pass this through to our general-purpose
            * error-handling service.
            *
            * @return \UserFrosting\Sprinkle\Core\Error\ExceptionHandlerManager
            */
            // TODO Implement properly
            'phpErrorHandler' => function (ExceptionHandlerManager $errorHandler) {
                return $errorHandler;
            },
        ];
    }
}
