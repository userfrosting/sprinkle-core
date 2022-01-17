<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

/*
 * Default testing config file for UserFrosting.  You may override/extend this in your site's configuration file to customize deploy settings.
 */
return [
    /*
     * Don't use persistent caching in tests
     */
    'cache' => [
        'illuminate' => [
            'default' => 'array',
        ],
    ],

    /*
     * Define in memory db for testing
     */
    'db' => [
        'default' => env('DB_TEST_CONNECTION', 'memory'),
    ],

    /*
     * Don't log deprecations in tests
     */
    'debug' => [
        'deprecation' => false,
        'queries'     => false,
        'exception'   => true,
    ],

    /*
    * Don't log exceptions in tests
    */
    'logs' => [
        'exception' => false,
    ],

    /*
     * Use testing filesystem for tests
     */
    'filesystems' => [
        'disks' => [
            'testing' => [
                'driver' => 'local',
                'root'   => 'storage/testing', //TODO : Replace with locator; \UserFrosting\STORAGE_DIR . \UserFrosting\DS . 'testing',
                'url'    => 'files/testing/',
            ],
            'testingDriver' => [
                'driver' => 'localTest',
                'root'   => 'storage/testingDriver', //TODO : Replace with locator; \UserFrosting\STORAGE_DIR . \UserFrosting\DS . 'testingDriver',
            ],
        ],
    ],

    /*
     * Disable native sessions in tests
     */
    'session' => [
        'handler' => env('TEST_SESSION_HANDLER', 'array'),
    ],
];
