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
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;

/**
 * Trait enabling wrapping of each test case in a database transaction
 * Based on Laravel `RefreshDatabase` Traits.
 */
trait RefreshDatabase
{
    /**
     * @var bool Indicates if the test database has been migrated.
     */
    public static bool $migrated = false;

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
    protected function refreshInMemoryDatabase(): void
    {
        $this->ci->get(Migrator::class)->run();
    }

    /**
     * Refresh a conventional test database.
     */
    protected function refreshTestDatabase(): void
    {
        if (!self::$migrated) {

            // Refresh the Database. Rollback all migrations and start over
            $this->ci->get(Migrator::class)->reset();
            $this->ci->get(Migrator::class)->migrate();

            self::$migrated = true;
        }

        // $this->beginDatabaseTransaction();
    }

    /**
     * Handle database transactions on the specified connections.
     */
    protected function beginDatabaseTransaction(): void
    {
        $database = $this->ci->get(Capsule::class);

        foreach ($this->connectionsToTransact() as $name) {
            $database->connection($name)->beginTransaction();
        }

        // TODO : Not used anymore !
        $this->beforeApplicationDestroyed(function () use ($database) {
            foreach ($this->connectionsToTransact() as $name) {
                $database->connection($name)->rollBack();
            }
        });
    }

    /**
     * The database connections that should have transactions.
     *
     * @return array
     */
    protected function connectionsToTransact(): array
    {
        return property_exists($this, 'connectionsToTransact')
                            ? $this->connectionsToTransact : [null];
    }
}
