<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery\Helper;

use Illuminate\Database\Connection;

/**
 * Database Test Trait. Include method to test the db connection
 */
trait DatabaseTest
{
    /** @Inject */
    protected Connection $connection;

    /**
     * Test database connection directly using PDO.
     *
     * TODO : Change Exception
     * @throws \Exception
     * @return bool       True if success
     */
    protected function testDB(): bool
    {
        try {
            $this->connection->getPdo();
        } catch (\PDOException $e) {
            $message = "Could not connect to the database '" . $this->connection->getName() . "' connection" . PHP_EOL;
            $message .= 'Exception: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
            $message .= 'Please check your database configuration and/or google the exception shown above and run command again.';
            throw new \Exception($message);
        }

        return true;
    }
}
