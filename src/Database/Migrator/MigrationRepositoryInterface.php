<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

/**
 * Migration Repository Interface.
 */
interface MigrationRepositoryInterface
{
    /**
     * Get the list of ran migrations.
     *
     * @param int|null $steps Number of batch to return. Null to return all.
     * @param bool     $asc   True for ascending order, false for descending.
     *
     * @return string[] An array of migration class names in the order they where ran
     */
    public function getMigrationsList(?int $steps = null, bool $asc = true): array;

    /**
     * Get details about a specific migration.
     *
     * @param string $migration The migration
     *
     * @return object The migration object
     */
    public function getMigration(string $migration): object;

    /**
     * Get the last migration batch in reserve order they were ran (last one first).
     *
     * @return string[]
     */
    public function getLast(): array;

    /**
     * Log that a migration was run.
     *
     * @param string $migration
     * @param int    $batch
     *
     * @return bool True if success
     */
    public function log(string $migration, int $batch): bool;

    /**
     * Remove a migration from the log.
     *
     * @param string $migration
     */
    public function delete(string $migration): void;

    /**
     * Get the next migration batch number.
     *
     * @return int
     */
    public function getNextBatchNumber(): int;

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber(): int;

    /**
     * Create the migration repository data store.
     */
    public function createRepository(): void;

    /**
     * Delete the migration repository data store.
     */
    public function deleteRepository(): void;

    /**
     * Determine if the migration repository exists.
     *
     * @return bool True for success, false for error.
     */
    public function repositoryExists(): bool;
}
