<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

/*
 * Debug development config file for UserFrosting. Sets every debug options on to help debug what's going wrong
 */
return [
    'assets' => [
        'use_raw' => true,
    ],
    'cache' => [
        'twig' => false,
    ],
    'debug' => [
        'deprecation'   => true,
        'queries'       => true,
        'smtp'          => true,
        'twig'          => true,
        'exception'     => true,
    ],
    'logs' => [
        'exception' => true,
    ],
    'site' => [
        'debug' => [
            'ajax' => true,
            'info' => true,
        ],
    ],
];
