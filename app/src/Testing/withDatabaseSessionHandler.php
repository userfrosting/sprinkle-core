<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Testing;

use Illuminate\Session\DatabaseSessionHandler;

/**
 * Trait used to run test against the `test_integration` db connection.
 */
trait withDatabaseSessionHandler
{
    /**
     * Reset CI with database session handler.
     */
    // TODO : Require session service...
    public function useDatabaseSessionHandler()
    {
        // Skip test if using in-memory database.
        // However we tell UF to use database session handler and in-memory
        // database, the session will always be created before the db can be
        // migrate, causing "table not found" errors
        if ($this->usingInMemoryDatabase()) {
            $this->markTestSkipped("Can't run this test on memory database");
        }

        // Force test to use database session handler
        putenv('TEST_SESSION_HANDLER=database');

        // Unset the env when test is done to avoid conflict
        // TODO : This doesn't exist anymore...
        $this->beforeApplicationDestroyedCallbacks[] = function () {
            putenv('TEST_SESSION_HANDLER');

            // Destroy session as we're switching handler anyway
            $this->ci->session->destroy();
        };

        // Refresh app to use new setup
        $this->ci->session->destroy();
        $this->refreshApplication();
        $this->setupTestDatabase(); //<-- N.B.: This is executed after the session is created on the default db...
        $this->refreshDatabase();

        // Make sure it worked
        if (!($this->ci->session->getHandler() instanceof DatabaseSessionHandler)) {
            $this->markTestSkipped('Session handler not an instance of DatabaseSessionHandler');
        }
    }
}
