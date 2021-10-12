<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

/*
 * Default develop config file for UserFrosting.
 */
return [
    /*
     * Define sqlite db in "_meta" for local development testing
     */
    // TODO : Move to main, or specific env var
    'db' => [
        'default' => [
            'driver'    => 'sqlite',
            'database'  => '_meta/database.sqlite',
        ],
    ],
];
