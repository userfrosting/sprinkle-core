<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Testing;

use DI\Container;
use Illuminate\Database\Connection;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;

/**
 * Trait o refresh database to fresh state.
 */
trait RefreshDatabase
{
    /**
     * Define hooks to migrate the database before and after each test.
     */
    public function refreshDatabase(): void
    {
        if (!isset($this->ci) || !$this->ci instanceof Container) {
            throw new \Exception('CI/Container not available. Make sure you extend the correct TestCase');
        }

        $this->usingInMemoryDatabase() ? $this->refreshInMemoryDatabase() : $this->refreshTestDatabase();
    }

    /**
     * Determine if an in-memory database is being used.
     *
     * @return bool
     */
    public function usingInMemoryDatabase(): bool
    {
        $connection = $this->ci->get(Connection::class);

        return $connection->getDatabaseName() === ':memory:';
    }

    /**
     * Refresh the in-memory database.
     */
    private function refreshInMemoryDatabase(): void
    {
        $this->ci->get(Migrator::class)->migrate();
    }

    /**
     * Refresh a conventional test database.
     */
    private function refreshTestDatabase(): void
    {
        // Refresh the Database. Rollback all migrations and start over
        $this->ci->get(Migrator::class)->reset();
        $this->ci->get(Migrator::class)->migrate();

        self::$migrated = true;
    }
}
