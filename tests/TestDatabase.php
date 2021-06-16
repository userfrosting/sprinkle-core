<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests;

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
        // Fetch services from CI
        $config = $this->ci->get(Config::class);
        $db = $this->ci->get(Capsule::class);

        // Setup connection
        $connection = $config->get('testing.dbConnection');
        $db->getDatabaseManager()->setDefaultConnection($connection);
    }
}
