<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

/*
 * Default production config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
 */
return [
    /*
     * `confirm_sensitive_command` in production mode
     */
    'bakery' => [
        'confirm_sensitive_command' => true,
    ],
    /*
     * Enable Twig cache
     */
    'cache' => [
        'twig' => true,
    ],
    /*
     * Turn off debug logs
     */
    'debug' => [
        'twig'      => false,
        'auth'      => false,
        'exception' => false,
    ],
    /*
    * Log error in production
    */
    'logs' => [
        'exception' => true,
    ],
    /*
     * Use router cache, disable full error details
     */
    'settings' => [
        'routerCacheFile'     => 'routes.cache',
    ],
    /*
     * Enable analytics, disable more debugging
     */
    'site' => [
        'analytics' => [
            'google' => [
                'enabled' => true,
            ],
        ],
        'debug' => [
            'ajax' => false,
            'info' => false,
        ],
        'uri' => [
            'public' => 'https://example.com', // This should be set in production to avoid errors !
        ],
    ],
    /*
     * Send errors to log
     */
    'php' => [
        'log_errors'      => true,
    ],
];
