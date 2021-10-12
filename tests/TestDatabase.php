<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests;

use DI\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Trait used to run test against the `test_integration` db connection
 */
trait TestDatabase
{
    /**
     * Define the test_integration database connection the default one.
     */
    public function setupTestDatabase(): void
    {
        if (!isset($this->ci) || !$this->ci instanceof Container) {
            throw new \Exception('CI/Container not available. Make sure you extend the correct TestCase');
        }

        // Fetch services from CI
        $config = $this->ci->get(Config::class);
        $db = $this->ci->get(Capsule::class);

        // Setup connection
        $connection = $config->get('testing.dbConnection');
        $db->getDatabaseManager()->setDefaultConnection($connection);
    }
}
