<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting;

// Directories and Paths
define('UserFrosting\DS', '/');

// The directory in which the non-public files reside.  Should be the same as the directory that this file is in.
if (!defined('UserFrosting\APP_DIR')) {
    define('UserFrosting\APP_DIR', str_replace(DIRECTORY_SEPARATOR, DS, __DIR__));
}

// The directory containing APP_DIR.  Usually, this will contain the entire website.
// define('UserFrosting\ROOT_DIR', realpath(__DIR__ . '/..'));

// Names of app directories
define('UserFrosting\APP_DIR_NAME', basename(__DIR__));
define('UserFrosting\SPRINKLES_DIR_NAME', 'sprinkles');

// Names of src directories within Sprinkles
define('UserFrosting\SRC_DIR_NAME', 'src');

// Full path to Sprinkles directory
define('UserFrosting\SPRINKLES_DIR', APP_DIR . DS . SPRINKLES_DIR_NAME);

// Full path to sprinkles schema file
define('UserFrosting\SPRINKLES_SCHEMA_FILE', APP_DIR . DS . 'sprinkles.json');

// Full path to system Bakery commands
define('UserFrosting\BAKERY_SYSTEM_DIR', APP_DIR . DS . 'system' . DS . 'Bakery' . DS . 'Command');

// Relative path from within sprinkle directory
define('UserFrosting\BAKERY_DIR', SRC_DIR_NAME . DS . 'Bakery');

// Names of root directories
define('UserFrosting\BUILD_DIR_NAME', 'build');
define('UserFrosting\PUBLIC_DIR_NAME', 'public');

// Names of app directories
define('UserFrosting\CACHE_DIR_NAME', 'cache');
define('UserFrosting\DB_DIR_NAME', 'database');
define('UserFrosting\LOG_DIR_NAME', 'logs');
define('UserFrosting\SESSION_DIR_NAME', 'sessions');
define('UserFrosting\VENDOR_DIR_NAME', 'vendor');

// Names of directories within Sprinkles
define('UserFrosting\ASSET_DIR_NAME', 'assets');
define('UserFrosting\EXTRA_DIR_NAME', 'extra');
define('UserFrosting\CONFIG_DIR_NAME', 'config');
define('UserFrosting\LOCALE_DIR_NAME', 'locale');
define('UserFrosting\ROUTE_DIR_NAME', 'routes');
define('UserFrosting\SCHEMA_DIR_NAME', 'schema');
define('UserFrosting\TEMPLATE_DIR_NAME', 'templates');
define('UserFrosting\FACTORY_DIR_NAME', 'factories');

// Full path to database directory (SQLite only)
define('UserFrosting\DB_DIR', APP_DIR . DS . DB_DIR_NAME);

// Full path to storage directories
define('UserFrosting\STORAGE_DIR', APP_DIR . DS . 'storage');
define('UserFrosting\STORAGE_PUBLIC_DIR', PUBLIC_DIR_NAME . DS . 'files');

// Full path to Composer's vendor directory
define('UserFrosting\VENDOR_DIR', APP_DIR . DS . VENDOR_DIR_NAME);

// Full path to frontend vendor asset directories
define('UserFrosting\ASSET_DIR', APP_DIR . DS . ASSET_DIR_NAME);
define('UserFrosting\NPM_ASSET_DIR', ASSET_DIR . DS . 'node_modules');
define('UserFrosting\BROWSERIFIED_ASSET_DIR', ASSET_DIR . DS . 'browser_modules');
define('UserFrosting\BOWER_ASSET_DIR', ASSET_DIR . DS . 'bower_components');

// Relative path from within sprinkle directory
define('UserFrosting\MIGRATIONS_DIR', SRC_DIR_NAME . DS . 'Database' . DS . 'Migrations');
define('UserFrosting\SEEDS_DIR', SRC_DIR_NAME . DS . 'Database' . DS . 'Seeds');
