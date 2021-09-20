<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Migrator;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Grammars\Grammar;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\SprinkleMigrationLocator;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;

/**
 * Tests for the Migrator Class
 *
 * Theses tests make sure the Migrator works correctly, without validating
 * against a simulated database. Those tests are performed by `DatabaseMigratorIntegrationTest`
 */
class MigratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Capsule | \Mockery\MockInterface $db;
    protected MigrationRepositoryInterface | \Mockery\MockInterface $repository;
    protected MigrationLocatorInterface | \Mockery\MockInterface $locator;

    /**
     * Setup base mock and migrator instance.
     */
    public function setUp(): void
    {
        // Boot parent TestCase
        parent::setUp();

        // Create mock objects
        $this->db = Mockery::mock(Capsule::class);
        $this->repository = Mockery::mock(MigrationRepositoryInterface::class);
        $this->locator = Mockery::mock(MigrationLocatorInterface::class);
    }

    protected function getMigrator(): Migrator
    {
       return new Migrator($this->db, $this->repository, $this->locator);
    }

    // public function testConstructor(): Migrator
    // {
    //     $migrator = $this->getMigrator();
    //     $this->assertInstanceOf(Migrator::class, $migrator);

    //     return $migrator;
    // }

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
        
        // Get mock
        $locator = Mockery::mock(MigrationLocatorInterface::class);
        
        // Set mock and test change
        $this->assertNotSame($locator, $migrator->getLocator());
        $migrator->setLocator($locator);
        $this->assertSame($locator, $migrator->getLocator());
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
