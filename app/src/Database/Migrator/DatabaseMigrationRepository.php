<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationNotFoundException;

/**
 * Repository used to store all migrations run against the database.
 */
class DatabaseMigrationRepository implements MigrationRepositoryInterface
{
    /**
     * @var string|null The connection name (default: null)
     */
    protected ?string $connection = null;

    /**
     * Create a new database migration repository instance.
     *
     * @param Capsule $db
     * @param string  $tableName
     */
    public function __construct(
        protected Capsule $db,
        protected string $tableName = 'migrations'
    ) {
    }

    /**
     * Get list of migrations, with all details regarding batch and cie.
     *
     * @param int|null $steps Number of batch to return. Null to return all.
     * @param bool     $asc   True for ascending order, false for descending.
     *
     * @return Collection Collection of migration from db in the order they where ran
     */
    public function all(?int $steps = null, bool $asc = true): Collection
    {
        $query = $this->getTable();

        if (!is_null($steps)) {
            $batch = max($this->getNextBatchNumber() - $steps, 1);
            $query = $query->where('batch', '>=', $batch);
        }

        $order = ($asc) ? 'asc' : 'desc';

        return $query->orderBy('id', $order)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function list(?int $steps = null, bool $asc = true): array
    {
        return $this->all($steps, $asc)->pluck('migration')->all();
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $migration): object
    {
        $result = $this->getTable()->where('migration', $migration)->first();

        // Throw error if null
        if ($result === null) {
            throw new MigrationNotFoundException();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $migration): bool
    {
        return $this->getTable()->where('migration', $migration)->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function last(): array
    {
        $query = $this->getTable()->where('batch', $this->getLastBatchNumber());

        return $query->orderBy('id', 'desc')->get()->pluck('migration')->all();
    }

    /**
     * {@inheritDoc}
     */
    public function log(string $migration, ?int $batch = null): bool
    {
        // If no batch number is provided, use next batch number.
        if ($batch === null) {
            $batch = $this->getNextBatchNumber();
        }

        return $this->getTable()->insert([
            'migration' => $migration,
            'batch'     => $batch,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $migration): void
    {
        $this->getTable()->where('migration', $migration)->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * {@inheritDoc}
     */
    public function getLastBatchNumber(): int
    {
        $batch = $this->getTable()->max('batch');

        // Default to 0 if it's null (empty table)
        return ($batch === null) ? 0 : intval($batch);
    }

    /**
     * {@inheritDoc}
     */
    public function create(): void
    {
        $this->getSchemaBuilder()->create($this->getTableName(), function (Blueprint $table) {
            // The migrations table is responsible for keeping track of which of the
            // migrations have actually run for the application. We'll create the
            // table to hold the migration file's path as well as the batch ID.
            $table->increments('id');
            // $table->string('sprinkle'); // TODO : Still required? No... But will it work with old install? require upgrade ?
            $table->string('migration');
            $table->integer('batch');
        });
    }

    /**
     * {@inheritDoc}
     */
    public function delete(): void
    {
        $this->getSchemaBuilder()->drop($this->getTableName());
    }

    /**
     * {@inheritDoc}
     */
    public function exists(): bool
    {
        try {
            return $this->getSchemaBuilder()->hasTable($this->getTableName());
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * Get a query builder for the migration table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getTable(): QueryBuilder
    {
        // Make sure repository exist
        if (!$this->exists()) {
            $this->create();
        }

        return $this->getConnection()->table($this->getTableName());
    }

    /**
     * Returns the schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    public function getSchemaBuilder(): Builder
    {
        return $this->getConnection()->getSchemaBuilder();
    }

    /**
     * Resolve the database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection(): Connection
    {
        return $this->db->getConnection($this->getConnectionName());
    }

    /**
     * Resolve the database connection instance.
     *
     * @return string|null The connection name (default: null)
     */
    public function getConnectionName(): ?string
    {
        return $this->connection;
    }

    /**
     * Set the information source to gather data.
     *
     * @param string|null $name The source name
     */
    public function setConnectionName(?string $name): void
    {
        $this->connection = $name;
    }

    /**
     * Get the migration table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Set the migration table name.
     *
     * @param string $tableName The migration table name
     *
     * @return self
     */
    public function setTableName(string $tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }
}
