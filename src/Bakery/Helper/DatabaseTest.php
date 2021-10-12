<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery\Helper;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Database Test Trait. Include method to test the db connection
 */
trait DatabaseTest
{
    /** @Inject */
    protected Capsule $capsule;

    /**
     * Function to test the db connection.
     *
     * TODO : Change Exception
     * @throws \Exception
     * @return bool       True if success
     */
    protected function testDB(): bool
    {
        // Check params are valid
        $dbParams = $this->config->get('db.default');
        if (!$dbParams) {
            throw new \Exception("'default' database connection not found.  Please double-check your configuration.");
        }

        // Test database connection directly using PDO
        try {
            $this->capsule::connection()->getPdo();
        } catch (\PDOException $e) {
            $message = "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}':" . PHP_EOL;
            $message .= 'Exception: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
            $message .= 'Please check your database configuration and/or google the exception shown above and run command again.';
            throw new \Exception($message);
        }

        return true;
    }
}
