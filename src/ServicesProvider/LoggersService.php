<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Log\MixedFormatter;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

// TODO Implement interface and untangle each one
class LoggersService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            /*
            * Debug logging with Monolog.
            *
            * Extend this service to push additional handlers onto the 'debug' log stack.
            *
            * @return \Monolog\Logger
            */
            'debugLogger' => function (ResourceLocatorInterface $locator) {
                $logger = new Logger('debug');

                $logFile = $locator->findResource('log://userfrosting.log', true, true);

                $handler = new StreamHandler($logFile);

                $formatter = new MixedFormatter(null, null, true);

                $handler->setFormatter($formatter);
                $logger->pushHandler($handler);

                return $logger;
            },

            /*
            * Error logging with Monolog.
            *
            * Extend this service to push additional handlers onto the 'error' log stack.
            *
            * @return \Monolog\Logger
            */
            'errorLogger' => function (ResourceLocatorInterface $locator) {
                $log = new Logger('errors');

                $logFile = $locator->findResource('log://userfrosting.log', true, true);

                $handler = new StreamHandler($logFile, Logger::WARNING);

                $formatter = new LineFormatter(null, null, true);

                $handler->setFormatter($formatter);
                $log->pushHandler($handler);

                return $log;
            },

            /*
             * Mail logging service.
             *
             * PHPMailer will use this to log SMTP activity.
             * Extend this service to push additional handlers onto the 'mail' log stack.
             *
             * @return \Monolog\Logger
             */
            'mailLogger' => function (ResourceLocatorInterface $locator) {
                $log = new Logger('mail');

                $logFile = $locator->findResource('log://userfrosting.log', true, true);

                $handler = new StreamHandler($logFile);
                $formatter = new LineFormatter(null, null, true);

                $handler->setFormatter($formatter);
                $log->pushHandler($handler);

                return $log;
            },

            /*
             * Laravel query logging with Monolog.
             *
             * Extend this service to push additional handlers onto the 'query' log stack.
             *
             * @return \Monolog\Logger
             */
            'queryLogger' => function (ResourceLocatorInterface $locator) {
                $logger = new Logger('query');

                $logFile = $locator->findResource('log://userfrosting.log', true, true);

                $handler = new StreamHandler($logFile);

                $formatter = new MixedFormatter(null, null, true);

                $handler->setFormatter($formatter);
                $logger->pushHandler($handler);

                return $logger;
            },
        ];
    }
}
