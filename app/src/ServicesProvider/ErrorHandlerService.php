<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Slim\Exception\HttpException;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerMiddleware;
use UserFrosting\Sprinkle\Core\Error\Handler\HttpExceptionHandler;

// use UserFrosting\Sprinkle\Core\Error\Handler\PhpMailerExceptionHandler;

class ErrorHandlerService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            /**
             * Register custom Handler.
             */
            ExceptionHandlerMiddleware::class => \DI\autowire()
                ->method('registerHandler', HttpException::class, HttpExceptionHandler::class, true)
                // ->method('registerHandler', \phpmailerException, PhpMailerExceptionHandler::class, true),
        ];
    }
}
