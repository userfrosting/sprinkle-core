<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Migrator;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\Grammar;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationDependencyNotMetException;

/**
 * Tests for the Migrator Class
 *
 * Theses tests make sure the Migrator works correctly, without validating
 * against a simulated database. Those tests are performed by `DatabaseMigratorIntegrationTest`
 */
class MigratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    // Shared mock objects
    protected MigrationRepositoryInterface | \Mockery\MockInterface $repository;
    protected MigrationLocatorInterface | \Mockery\MockInterface $locator;
    protected Connection | \Mockery\MockInterface $connection;

    /**
     * Setup base mock and migrator instance.
     */
    public function setUp(): void
    {
        // Boot parent TestCase
        parent::setUp();

        // Create mock objects
        $this->connection = Mockery::mock(Connection::class);
        $this->repository = Mockery::mock(MigrationRepositoryInterface::class);
        $this->locator = Mockery::mock(MigrationLocatorInterface::class);
    }

    protected function getMigrator(): Migrator
    {
        return new Migrator($this->repository, $this->locator, $this->connection);
    }

    public function testConstructor(): Migrator
    {
        $migrator = $this->getMigrator();
        $this->assertInstanceOf(Migrator::class, $migrator);

        return $migrator;
    }

    /**
     * @depends testConstructor
     *
     * @param Migrator $migrator
     */
    public function testRepositoryMethods(Migrator $migrator): void
    {
        // Assert get repo from the main one
        $this->assertInstanceOf(MigrationRepositoryInterface::class, $migrator->getRepository());

        // Get mock
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('exists')->twice()->andReturn(true, false)
            ->getMock();

        // Set mock and test change
        $this->assertNotSame($repository, $migrator->getRepository());
        $migrator->setRepository($repository);
        $this->assertSame($repository, $migrator->getRepository());

        // Assert `repositoryExists` mock
        $this->assertTrue($migrator->repositoryExists());
        $this->assertFalse($migrator->repositoryExists());
    }

    /**
     * @depends testConstructor
     *
     * @param Migrator $migrator
     */
    public function testLocatorMethods(Migrator $migrator): void
    {
        // Assert get locator from the main one
        $this->assertInstanceOf(MigrationLocatorInterface::class, $migrator->getLocator());

        // Get new mock
        $locator = Mockery::mock(MigrationLocatorInterface::class);

        // Set new mock and test change
        $this->assertNotSame($locator, $migrator->getLocator());
        $migrator->setLocator($locator);
        $this->assertSame($locator, $migrator->getLocator());
    }

    /**
     * @depends testConstructor
     *
     * @param Migrator $migrator
     */
    public function testConnectionMethods(Migrator $migrator): void
    {
        // Assert get Connection from the main one
        $this->assertInstanceOf(Connection::class, $migrator->getConnection());

        // Get new mock
        $connection = Mockery::mock(Connection::class);

        // Set new mock and test change
        $this->assertNotSame($connection, $migrator->getConnection());
        $migrator->setConnection($connection);
        $this->assertSame($connection, $migrator->getConnection());
    }

    /**
     * @depends testConstructor
     *
     * @param Migrator $migrator
     */
    public function testMigrate(Migrator $migrator): void
    {
        // Mock a migration for locator
        $migration1 = Mockery::mock(MigrationInterface::class)
            ->shouldReceive('up')->once()->andReturn(null)
            ->getMock();
        $migration2 = Mockery::mock(MigrationInterface::class)
            ->shouldReceive('up')->once()->andReturn(null)
            ->getMock();

        // Create new repository mock for batch call and log
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('getNextBatchNumber')->once()->andReturn(2)
            ->shouldReceive('log')->with($migration1::class, 2)->once()->andReturn(true)
            ->shouldReceive('log')->with($migration2::class, 2)->once()->andReturn(true)
            ->getMock();

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration1::class)->once()->andReturn($migration1)
            ->shouldReceive('get')->with($migration2::class)->once()->andReturn($migration2)
            ->getMock();

        // We mock connection and Grammar, to simulate `supportsSchemaTransactions`
        $grammar = Mockery::mock(Grammar::class)
            ->shouldReceive('supportsSchemaTransactions')->times(2)->andReturn(false)
            ->getMock();
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('getSchemaGrammar')->times(2)->andReturn($grammar)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$repository, $locator, $connection])->makePartial();
        $migrator->shouldReceive('getPending')->once()->andReturn([$migration1::class, $migration2::class]);

        // Migrate (Step = false)
        $result = $migrator->migrate();

        // Assert results
        $this->assertSame([$migration1::class, $migration2::class], $result);
    }

    /**
     * N.B.: Transaction is only tested for behavior. 
     * 
     * @depends testConstructor
     * @depends testMigrate
     *
     * @param Migrator $migrator
     */
    public function testMigrateWithStepsAndTransaction(Migrator $migrator): void
    {
        // Mock a migration for locator
        $migration1 = Mockery::mock(MigrationInterface::class);
        $migration2 = Mockery::mock(MigrationInterface::class);

        // Create new repository mock for batch call and log
        $repository = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('getNextBatchNumber')->once()->andReturn(2)
            ->shouldReceive('log')->with($migration1::class, 2)->once()->andReturn(true)
            ->shouldReceive('log')->with($migration2::class, 3)->once()->andReturn(true) // 3, as steps is true
            ->getMock();

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration1::class)->once()->andReturn($migration1)
            ->shouldReceive('get')->with($migration2::class)->once()->andReturn($migration2)
            ->getMock();

        // We mock connection and Grammar, to simulate `supportsSchemaTransactions`
        $grammar = Mockery::mock(Grammar::class)
            ->shouldReceive('supportsSchemaTransactions')->times(2)->andReturn(true)
            ->getMock();
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('getSchemaGrammar')->times(2)->andReturn($grammar)
            ->shouldReceive('transaction')->times(2)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$repository, $locator, $connection])->makePartial();
        $migrator->shouldReceive('getPending')->once()->andReturn([$migration1::class, $migration2::class]);

        // Migrate (Step = true)
        $result = $migrator->migrate(step: true);

        // Assert results
        $this->assertSame([$migration1::class, $migration2::class], $result);
    }

    public function testMigrateWithNoPending(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getPending')->once()->andReturn([]);

        $result = $migrator->migrate();
        $this->assertSame([], $result);
    }

    public function testMigrateWithPendingException(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getPending')->once()->andThrow(new MigrationDependencyNotMetException());

        $this->expectException(MigrationDependencyNotMetException::class);
        $migrator->migrate();
    }

    public function testPretendToMigrate(): void
    {
        // Return from Connection pretend.
        // The actual data here is not 100% true, see Integration test for it.
        $queries = [['query' => 'foo']];

        // Mock a migration for locator
        $migration = Mockery::mock(MigrationInterface::class);

        // Create new locator with our mock migration
        $locator = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('get')->with($migration::class)->once()->andReturn($migration)
            ->getMock();

        // Up is not called, as the callable passed to pretend is not mocked here
        // See Integration test for a more complete test.
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('pretend')->once()->andReturn($queries)
            ->getMock();

        // Create partial mock of migrator, so we can spoof "getPending"
        $migrator = Mockery::mock(Migrator::class, [$this->repository, $locator, $connection])->makePartial();
        $migrator->shouldReceive('getPending')->once()->andReturn([$migration::class]);

        // Pretend to migrate
        $result = $migrator->pretendToMigrate();

        // Assert results
        $this->assertSame([
            $migration::class => $queries
        ], $result);
    }

    public function testPretendToMigrateWithNoPending(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getPending')->once()->andReturn([]);

        $result = $migrator->pretendToMigrate();
        $this->assertSame([], $result);
    }

    public function testPretendToMigrateWithPendingException(): void
    {
        $migrator = Mockery::mock(Migrator::class)->makePartial();
        $migrator->shouldReceive('getPending')->once()->andThrow(new MigrationDependencyNotMetException());

        $this->expectException(MigrationDependencyNotMetException::class);
        $migrator->pretendToMigrate();
    }

    /**
     * Basic test to make sure the base method syntax is ok
     */
    // public function testMigratorUpWithNoMigrations()
    // {
    //     // Locator will be asked to return the available migrations
    //     $this->locator->shouldReceive('getMigrations')->once()->andReturn([]);

    //     // Repository will be asked to return the ran migrations
    //     $this->repository->shouldReceive('list')->once()->andReturn([]);

    //     $migrations = $this->migrator->run();
    //     $this->assertEmpty($migrations);
    // }

    /**
     * Basic test where all available migrations are pending and fulfillable
     */
    // public function testMigratorUpWithOnlyPendingMigrations()
    // {
    //     // The migrations set
    //     $testMigrations = [
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
    //     ];

    //     // When running up, Locator will return all 3 migration classes
    //     $this->locator->shouldReceive('getMigrations')->andReturn($testMigrations);

    //     // Repository will be asked to return the ran migrations, the next batch number and will log 3 new migrations
    //     $this->repository->shouldReceive('list')->andReturn([]);
    //     $this->repository->shouldReceive('getNextBatchNumber')->andReturn(1);
    //     $this->repository->shouldReceive('log')->times(3)->andReturn(null);

    //     // SchemaBuilder will create all 3 tables
    //     $this->schema->shouldReceive('create')->times(3)->andReturn(null);

    //     // Connection will be asked for the SchemaGrammar
    //     $grammar = m::mock(Grammar::class);
    //     $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
    //     $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

    //     // Run migrations up
    //     $migrations = $this->migrator->run();

    //     // All classes should have been migrated
    //     $this->assertEquals($testMigrations, $migrations);
    // }

    /**
     * Test where one of the available migrations is already installed
     */
    // public function testMigratorUpWithOneInstalledMigrations()
    // {
    //     // When running up, Locator will return all 3 migration classes
    //     $this->locator->shouldReceive('getMigrations')->andReturn([
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
    //     ]);

    //     // Repository will be asked to return the ran migrations (one), the next batch number and will log 2 new migrations
    //     $this->repository->shouldReceive('list')->andReturn([
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //     ]);
    //     $this->repository->shouldReceive('getNextBatchNumber')->andReturn(2);
    //     $this->repository->shouldReceive('log')->times(2)->andReturn(null);

    //     // SchemaBuilder will only create 2 tables
    //     $this->schema->shouldReceive('create')->times(2)->andReturn(null);

    //     // Connection will be asked for the SchemaGrammar
    //     $grammar = m::mock(Grammar::class);
    //     $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
    //     $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

    //     // Run migrations up
    //     $migrations = $this->migrator->run();

    //     // The migration already ran shouldn't be in the pending ones
    //     $this->assertEquals([
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
    //     ], $migrations);
    // }

    /**
     * Test where all available migrations have been ran
     */
    // public function testMigratorUpWithNoPendingMigrations()
    // {
    //     // The migrations set
    //     $testMigrations = [
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
    //     ];

    //     // When running up, Locator will return all 3 migration classes
    //     $this->locator->shouldReceive('getMigrations')->andReturn($testMigrations);

    //     // Repository will be asked to return the ran migrations (one), the next batch number and will log 2 new migrations
    //     $this->repository->shouldReceive('list')->andReturn($testMigrations);
    //     $this->repository->shouldNotReceive('getNextBatchNumber');
    //     $this->repository->shouldNotReceive('log');

    //     // SchemaBuilder will only create 2 tables
    //     $this->schema->shouldNotReceive('create');

    //     // Run migrations up
    //     $migrations = $this->migrator->run();

    //     // The migration already ran shouldn't be in the pending ones
    //     $this->assertEquals([], $migrations);
    // }

    /**
     * Test where one of the available migrations is missing a dependency
     */
    //!TODO

    /**
     * Test rolling back where no migrations have been ran
     */
    // public function testMigratorRollbackWithNoInstalledMigrations()
    // {
    //     // Repository will be asked to return the last batch of ran migrations
    //     $this->repository->shouldReceive('last')->andReturn([]);

    //     // Run migrations up
    //     $migrations = $this->migrator->rollback();

    //     // The migration already ran shouldn't be in the pending ones
    //     $this->assertEquals([], $migrations);
    // }

    /**
     * Test rolling back all installed migrations
     */
    // public function testMigratorRollbackAllInstalledMigrations()
    // {
    //     // The migrations set
    //     $testMigrations = [
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
    //     ];

    //     // When running up, Locator will return all 3 migration classes
    //     $this->locator->shouldReceive('getMigrations')->once()->andReturn($testMigrations);

    //     // Repository will be asked to return the ran migrations (one), the next batch number and will log 2 new migrations
    //     $this->repository->shouldReceive('last')->once()->andReturn($testMigrations);
    //     $this->repository->shouldReceive('list')->once()->andReturn($testMigrations);
    //     $this->repository->shouldReceive('delete')->times(3)->andReturn([]);

    //     // SchemaBuilder will only create 2 tables
    //     $this->schema->shouldReceive('dropIfExists')->times(3)->andReturn([]);

    //     // Connection will be asked for the SchemaGrammar
    //     $grammar = m::mock(Grammar::class);
    //     $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
    //     $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

    //     // Run migrations up
    //     $migrations = $this->migrator->rollback();

    //     // The migration already ran shouldn't be in the pending ones
    //     $this->assertEquals($testMigrations, $migrations);
    // }

    /**
     * Test where one of the installed migration is not in the available migration classes
     */
    // public function testMigratorRollbackAllInstalledMigrationsWithOneMissing()
    // {
    //     // Locator will only return one of the two installed migrations
    //     $this->locator->shouldReceive('getMigrations')->once()->andReturn([
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //     ]);

    //     // Repository will be asked to return the ran migrations (two of them)
    //     // and will only be asked to delete one
    //     $installed = [
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //     ];
    //     $this->repository->shouldReceive('last')->once()->andReturn($installed);
    //     $this->repository->shouldReceive('list')->once()->andReturn($installed);
    //     $this->repository->shouldReceive('delete')->times(1)->andReturn([]);

    //     // SchemaBuilder will only drop one of the 2 tables
    //     $this->schema->shouldReceive('dropIfExists')->times(1)->andReturn([]);

    //     // Connection will be asked for the SchemaGrammar
    //     $grammar = m::mock(Grammar::class);
    //     $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
    //     $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

    //     // Rollback migrations
    //     $migrations = $this->migrator->rollback();

    //     // The migration not available from the locator shouldn't have been run
    //     $this->assertEquals([
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //     ], $migrations);
    // }

    /**
     * Test a specific migration with no dependencies can be rolled back
     */
    // public function testMigratorRollbackSpecific()
    // {
    //     // The installed / available migrations
    //     $testMigrations = [
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
    //     ];

    //     // Migration object for the one being deleted
    //     $migration = '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable';
    //     $migrationObject = (object) [
    //         'migration' => $migration,
    //     ];

    //     // Locator will return all 3 migration classes as available
    //     $this->locator->shouldReceive('getMigrations')->once()->andReturn($testMigrations);

    //     // Repository will be asked to return the ran migrations and delete one
    //     $this->repository->shouldReceive('get')->once()->andReturn($migrationObject);
    //     $this->repository->shouldReceive('list')->once()->andReturn($testMigrations);
    //     $this->repository->shouldReceive('delete')->times(1)->andReturn([]);

    //     // SchemaBuilder will delete 1 table
    //     $this->schema->shouldReceive('dropIfExists')->times(1)->andReturn([]);

    //     // Connection will be asked for the SchemaGrammar
    //     $grammar = m::mock(Grammar::class);
    //     $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
    //     $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

    //     // Rollback only the Flights table. Should work as no other depends on it
    //     $rolledback = $this->migrator->rollbackMigration($migration);

    //     // The migration already ran shouldn't be in the pending ones
    //     $this->assertEquals([$migration], $rolledback);
    // }

    /**
     * Test a specific migration with some dependencies can be rolled back
     */
    // public function testMigratorRollbackSpecificWithDependencies()
    // {
    //     // The installed / available migrations
    //     $testMigrations = [
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
    //     ];

    //     // Migration object for the one being deleted
    //     $migration = '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable';
    //     $migrationObject = (object) [
    //         'migration' => $migration,
    //     ];

    //     // Locator will return all 3 migration classes as available
    //     $this->locator->shouldReceive('getMigrations')->once()->andReturn($testMigrations);

    //     // Repository will be asked to return the ran migrations and delete one
    //     $this->repository->shouldReceive('get')->once()->andReturn($migrationObject);
    //     $this->repository->shouldReceive('list')->once()->andReturn($testMigrations);
    //     $this->repository->shouldNotReceive('delete');

    //     // SchemaBuilder will delete 1 table
    //     $this->schema->shouldNotReceive('dropIfExists');

    //     // Connection will be asked for the SchemaGrammar
    //     $grammar = m::mock(Grammar::class);
    //     $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
    //     $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

    //     // Rollback only the user table. Should fail as the flight table depends on it
    //     $this->expectException(\Exception::class);
    //     $rolledback = $this->migrator->rollbackMigration($migration);
    // }

    /**
     * Test where one of the installed migration is not in the available migration classes
     */
    // public function testMigratorResetAllInstalledMigrations()
    // {
    //     // The migrations set
    //     $testMigrations = [
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
    //     ];

    //     // Locator will return all 3 migration classes
    //     $this->locator->shouldReceive('getMigrations')->once()->andReturn($testMigrations);

    //     // Repository will be asked to return the ran migrations (all of them),
    //     // then asked to delete all 3 of them
    //     $this->repository->shouldReceive('list')->twice()->andReturn($testMigrations);
    //     $this->repository->shouldReceive('delete')->times(3)->andReturn([]);

    //     // SchemaBuilder will drop all 3 tables
    //     $this->schema->shouldReceive('dropIfExists')->times(3)->andReturn([]);

    //     // Connection will be asked for the SchemaGrammar
    //     $grammar = m::mock(Grammar::class);
    //     $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
    //     $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

    //     // Reset migrations
    //     $migrations = $this->migrator->reset();

    //     // All the migrations should have been rolledback
    //     $this->assertEquals(array_reverse($testMigrations), $migrations);
    // }
}
