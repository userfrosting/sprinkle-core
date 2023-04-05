<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Migrator;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationNotFoundException;

/**
 * DatabaseMigrationRepository Test
 */
class DatabaseMigrationRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Builder | \Mockery\MockInterface $schemaBuilder;
    protected QueryBuilder | \Mockery\MockInterface $queryBuilder;
    protected Connection | \Mockery\MockInterface $connection;
    protected Manager | \Mockery\MockInterface $capsule;

    public function setUp(): void
    {
        parent::setUp();

        // Create mock objects and their common expectations
        $this->schemaBuilder = Mockery::mock(Builder::class)
            ->shouldReceive('hasTable')->with('Migrafoo')->andReturn(true)->byDefault()
            ->getMock();
        $this->queryBuilder = Mockery::mock(QueryBuilder::class);
        $this->connection = Mockery::mock(Connection::class)
            ->shouldReceive('getSchemaBuilder')->andReturn($this->schemaBuilder)
            ->shouldReceive('table')->with('Migrafoo')->andReturn($this->queryBuilder)
            ->getMock();
        $this->capsule = Mockery::mock(Manager::class)
            ->shouldReceive('getConnection')->andReturn($this->connection)
            ->getMock();
    }

    protected function getRepo(): DatabaseMigrationRepository
    {
        return new DatabaseMigrationRepository($this->capsule, 'Migrafoo');
    }

    public function testConstructor(): DatabaseMigrationRepository
    {
        // Create repository instance
        $repository = $this->getRepo();

        // Make first assertion about class creation.
        $this->assertInstanceOf(MigrationRepositoryInterface::class, $repository);

        return $repository;
    }

    /**
     * @depends testConstructor
     *
     * @param DatabaseMigrationRepository $repository
     */
    public function testTableNameGetterAndSetter(DatabaseMigrationRepository $repository): void
    {
        $this->assertSame('Migrafoo', $repository->getTableName());
        $this->assertSame('MigraBar', $repository->setTableName('MigraBar')->getTableName());

        // Set it back to avoid messing with Mock ;)
        $repository->setTableName('Migrafoo');
    }

    /**
     * @depends testConstructor
     *
     * @param DatabaseMigrationRepository $repository
     */
    public function testGetConnection(DatabaseMigrationRepository $repository): void
    {
        $this->assertNull($repository->getConnectionName());
        $this->assertInstanceOf(Connection::class, $repository->getConnection());
    }

    /**
     * @depends testConstructor
     * @depends testGetConnection
     *
     * @param DatabaseMigrationRepository $repository
     */
    public function testSetConnectionName(DatabaseMigrationRepository $repository): void
    {
        $repository->setConnectionName('foo');
        $this->assertSame('foo', $repository->getConnectionName());
        $this->assertInstanceOf(Connection::class, $repository->getConnection());
    }

    /**
     * @depends testConstructor
     *
     * @param DatabaseMigrationRepository $repository
     */
    public function testGetSchemaBuilder(DatabaseMigrationRepository $repository): void
    {
        $this->assertInstanceOf(Builder::class, $repository->getSchemaBuilder());
    }

    /**
     * @depends testConstructor
     *
     * @param DatabaseMigrationRepository $repository
     */
    public function testGetTable(DatabaseMigrationRepository $repository): void
    {
        $this->assertInstanceOf(QueryBuilder::class, $repository->getTable());
    }

    /**
     * @depends testGetSchemaBuilder
     */
    public function testRepositoryCreation(): void
    {
        // Set mock expectations
        $this->schemaBuilder
            ->shouldReceive('hasTable')->with('Migrafoo')->once()->andReturn(true)
            ->shouldReceive('create')->with('Migrafoo', \Closure::class)->once()
            ->shouldReceive('drop')->with('Migrafoo')->once();

        // Get new repo with above expectations
        $repository = $this->getRepo();

        $this->assertTrue($repository->exists());

        // Warning : No assertions done here, only mock expectations check.
        // See Integration test for exception handling.
        $repository->create();
        $repository->delete();
    }

    /**
     * @depends testGetTable
     * @depends testRepositoryCreation
     */
    public function testGetTableNoExist(): void
    {
        $this->schemaBuilder
            ->shouldReceive('hasTable')->once()->andReturn(false)
            ->shouldReceive('create')->with('Migrafoo', \Closure::class)->once();
        $this->assertInstanceOf(QueryBuilder::class, $this->getRepo()->getTable());
    }

    /**
     * @depends testRepositoryCreation
     */
    public function testRepositoryHasTableFalse(): void
    {
        // Set mock expectations
        $this->schemaBuilder
            ->shouldReceive('hasTable')->with('Migrafoo')->once()->andReturn(false);

        // Get new repo with above expectations
        $repository = $this->getRepo();

        $this->assertFalse($repository->exists());
    }

    /**
     * @depends testRepositoryCreation
     */
    public function testRepositoryHasTableThrowException(): void
    {
        // Set mock expectations
        $exception = Mockery::mock(QueryException::class);
        $this->schemaBuilder
            ->shouldReceive('hasTable')->with('Migrafoo')->once()->andThrow($exception);

        // Get new repo with above expectations
        $repository = $this->getRepo();

        $this->assertFalse($repository->exists());
    }

    /**
     * @depends testGetTable
     */
    public function testGetLastBatchNumberAndGetNextBatchNumber(): void
    {
        // Set mock expectations
        $this->queryBuilder
            ->shouldReceive('max')->twice()->with('batch')->andReturn(3);

        // Get new repo with above expectations
        $repository = $this->getRepo();

        $this->assertSame(3, $repository->getLastBatchNumber());
        $this->assertSame(4, $repository->getNextBatchNumber());
    }

    /**
     * @depends testGetLastBatchNumberAndGetNextBatchNumber
     *
     * N.B.: Empty table should return null on the max query.
     */
    public function testGetLastBatchNumberForEmptyTable(): void
    {
        // Set mock expectations
        $this->queryBuilder
            ->shouldReceive('max')->once()->with('batch')->andReturn(null);

        // Get new repo with above expectations
        $repository = $this->getRepo();

        $this->assertSame(0, $repository->getLastBatchNumber());
    }

    /**
     * @depends testGetTable
     */
    public function testGetMigrationsAndList(): void
    {
        // Set mock expectations
        $result = new Collection([
            ['migration' => 'bar']
        ]);

        $this->queryBuilder
            ->shouldReceive('orderBy')->once()->with('id', 'asc')->andReturn($this->queryBuilder)
            ->shouldReceive('get')->once()->andReturn($result);

        // Get new repo with above expectations
        $repository = $this->getRepo();

        $this->assertSame(['bar'], $repository->list());
    }

    /**
     * @depends testGetMigrationsAndList
     */
    public function testGetMigrationsWithStepsAndDesc(): void
    {
        // Set mock expectations
        $result = new Collection([
            ['migration' => 'bar'],
            ['migration' => 'foo'],
        ]);

        $this->queryBuilder
            ->shouldReceive('max')->once()->with('batch')->andReturn(3)
            ->shouldReceive('orderBy')->once()->with('id', 'desc')->andReturn($this->queryBuilder)
            ->shouldReceive('get')->once()->andReturn($result)
            ->shouldReceive('where')->once()->with('batch', '>=', 3)->andReturn($this->queryBuilder);

        // Get new repo with above expectations
        $repository = $this->getRepo();

        $this->assertSame(['bar', 'foo'], $repository->list(1, false));
    }

    /**
     * @depends testGetMigrationsAndList
     * This test should to be completed with an actual integration test
     */
    public function testGet(): void
    {
        // Set mock expectations
        $result = new \stdClass(['migration' => 'foo', 'batch' => 1]);

        $this->queryBuilder
            ->shouldReceive('where')->with('migration', 'foo')->twice()->andReturn($this->queryBuilder)
            ->shouldReceive('first')->once()->andReturn($result)
            ->shouldReceive('exists')->once()->andReturn(true);

        // Get new repo with above expectations
        $repository = $this->getRepo();

        // This is mostly mock expectations check. See Integration test
        $this->assertTrue($repository->has('foo'));
        $migration = $repository->get('foo');
        $this->assertIsObject($migration);
        $this->assertSame($result, $migration);
    }

    /**
     * @depends testGet
     */
    public function testGetWithNull(): void
    {
        $this->queryBuilder
            ->shouldReceive('where')->with('migration', 'foo')->twice()->andReturn($this->queryBuilder)
            ->shouldReceive('first')->once()->andReturn(null)
            ->shouldReceive('exists')->once()->andReturn(false);

        // Get new repo with above expectations
        $repository = $this->getRepo();

        // Mock first() return a null
        $this->assertFalse($repository->has('foo'));
        $this->expectException(MigrationNotFoundException::class);
        $repository->get('foo');
    }

    public function testLast(): void
    {
        // Set mock expectations
        $result = new Collection([
            ['migration' => 'foo', 'batch' => 2],
            ['migration' => 'bar', 'batch' => 2],
        ]);

        $this->queryBuilder
            ->shouldReceive('max')->once()->with('batch')->andReturn(2)
            ->shouldReceive('where')->once()->with('batch', 2)->andReturn($this->queryBuilder)
            ->shouldReceive('orderBy')->once()->with('id', 'desc')->andReturn($this->queryBuilder)
            ->shouldReceive('get')->once()->andReturn($result);

        // Get new repo with above expectations
        $repository = $this->getRepo();

        $this->assertSame(['foo', 'bar'], $repository->last());
    }

    /**
     * Warning: This test should to be completed with an actual integration test
     */
    public function testLogAndDelete(): void
    {
        $this->queryBuilder
            ->shouldReceive('insert')->once()->with(['migration' => 'foo', 'batch' => 2])->andReturn(true)
            ->shouldReceive('where')->once()->with('migration', 'foobar')->andReturn($this->queryBuilder)
            ->shouldReceive('delete')->once();
        $repository = $this->getRepo();

        $this->assertTrue($repository->log('foo', 2));
        $this->assertNull($repository->remove('foobar'));
    }

    public function testLogNoBatchNumber(): void
    {
        $this->queryBuilder
            ->shouldReceive('insert')->once()->with(['migration' => 'foo', 'batch' => 2])->andReturn(true)
            ->shouldReceive('max')->once()->with('batch')->andReturn(1);
        $repository = $this->getRepo();

        $this->assertTrue($repository->log('foo'));
    }
}
