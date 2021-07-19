<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Support\Repository\Repository as Config;

class TestDatabaseTraitTest extends TestCase
{
    use TestDatabase;

    /**
     * Setup TestDatabase
     */
    public function setUp(): void
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
    }

    /**
     * Test the TestDatabase traits works
     */
    public function testTrait()
    {
        // Fetch services from CI
        $config = $this->ci->get(Config::class);
        $db = $this->ci->get(Capsule::class);

        // Use the testing db for this test
        $connection = $db->getConnection();
        $this->assertEquals($config->get('testing.dbConnection'), $connection->getName());
    }
}
