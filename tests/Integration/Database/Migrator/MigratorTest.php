<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database\Migrator;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Testing\TestCase;

/**
 * Migrator Tests
 */
class MigratorTest extends TestCase
{
    use TestDatabase;

    protected string $mainSprinkle = TestMigrateSprinkle::class;

    /**
     * @var Builder
     */
    protected Builder $schema;

    /**
     * Setup migration instances used for all tests
     */
    public function setUp(): void
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();

        // Alias schema Builder
        $this->schema = $this->ci->get(Builder::class);
    }

    public function testConstructor(): Migrator
    {
        /** @var Migrator */
        $migrator = $this->ci->get(Migrator::class);

        // Test Constructor
        $this->assertInstanceOf(Migrator::class, $migrator);
        $this->assertInstanceOf(MigrationRepositoryInterface::class, $migrator->getRepository());
        $this->assertInstanceOf(MigrationLocatorInterface::class, $migrator->getLocator());

        // Test repository exist for next assertions
        $config = $this->ci->get(Config::class);
        $migrationTable = $config->get('migrations.repository_table');
        $this->assertFalse($this->schema->hasTable($migrationTable));
        $this->assertFalse($migrator->getRepository()->exists());
        $migrator->getRepository()->create();
        $this->assertTrue($this->schema->hasTable($migrationTable));
        $this->assertTrue($migrator->getRepository()->exists());

        return $migrator;
    }

    /**
     * @depends testConstructor
     */
    public function testPretendToMigrate(Migrator $migrator): void
    {
        $result = $migrator->pretendToMigrate();

        // Assert results
        // N.B.: Don't assert exact string here, because it could change depending
        //       of DB, we only assert structure for now.
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertIsString($result[StubMigrationA::class][0]['query']);
    }

    /**
     * @depends testConstructor
     */
    public function testMigrate(Migrator $migrator): void
    {
        $result = $migrator->migrate();

        // Assert results
        $this->assertSame([StubMigrationA::class], $result);

        // Assert table has been created
        // N.B.: Requires to get schema from connection, as otherwise it might
        // not work (different :memory: instance)
        $schema = $migrator->getConnection()->getSchemaBuilder();
        $this->assertTrue($schema->hasTable('test'));
    }

    // public function testBasicMigration(): void
    // {
    //     $ran = $this->migrator->run();

    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));

    //     $this->assertEquals([
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //     ], $ran);
    // }

    // TODO : This didn't worked on GH Actions... testing.dbConnection might not be working because db is initialized before.
    // https://github.com/userfrosting/sprinkle-core/runs/2925978557?check_suite_focus=true#step:11:709
    // public function testRepository(): void
    // {
    //     $ran = $this->migrator->run();

    //     $expected = [
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //     ];

    //     // Theses assertions makes sure the repository and the migration returns the same format
    //     // N.B.: last return the migrations in reverse order (last ran first)
    //     $this->assertEquals($expected, $ran);
    //     $this->assertEquals(array_reverse($expected), $this->repository->last());
    //     $this->assertEquals($expected, $this->repository->list());
    // }

    // public function testMigrationsCanBeRolledBack(): void
    // {
    //     // Run up
    //     $this->migrator->run();
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));

    //     $rolledBack = $this->migrator->rollback();
    //     $this->assertFalse($this->schema->hasTable('users'));
    //     $this->assertFalse($this->schema->hasTable('password_resets'));

    //     // Make sure the data returned from migrator is accurate.
    //     // N.B.: The order returned by the rollback method is ordered by which
    //     // migration was rolled back first (reversed from the order they where ran up)
    //     $this->assertEquals(array_reverse($this->migrationLocator->all()), $rolledBack);
    // }

    // public function testMigrationsCanBeReset(): void
    // {
    //     // Run up
    //     $this->migrator->run();
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));

    //     $rolledBack = $this->migrator->reset();
    //     $this->assertFalse($this->schema->hasTable('users'));
    //     $this->assertFalse($this->schema->hasTable('password_resets'));

    //     // Make sure the data returned from migrator is accurate.
    //     $this->assertEquals(array_reverse($this->migrationLocator->all()), $rolledBack);
    // }

    // public function testNoErrorIsThrownWhenNoOutstandingMigrationsExist(): void
    // {
    //     $this->migrator->run();
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));
    //     $this->migrator->run();
    // }

    // public function testNoErrorIsThrownWhenNothingToRollback(): void
    // {
    //     $this->migrator->run();
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));
    //     $this->migrator->rollback();
    //     $this->assertFalse($this->schema->hasTable('users'));
    //     $this->assertFalse($this->schema->hasTable('password_resets'));
    //     $this->migrator->rollback();
    // }

    // public function testPretendUp(): void
    // {
    //     $result = $this->migrator->run(['pretend' => true]);
    //     $notes = $this->migrator->getNotes();
    //     $this->assertFalse($this->schema->hasTable('users'));
    //     $this->assertFalse($this->schema->hasTable('password_resets'));
    //     $this->assertNotEquals([], $notes);
    // }

    // public function testPretendRollback(): void
    // {
    //     // Run up as usual
    //     $result = $this->migrator->run();
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));

    //     $expected = [
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
    //     ];

    //     $rolledBack = $this->migrator->rollback(['pretend' => true]);
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));
    //     $this->assertEquals(array_reverse($expected), $rolledBack);
    // }

    // public function testWithInvalidClass(): void
    // {
    //     // Change the repository so we can test with the InvalidMigrationLocatorStub
    //     $locator = new InvalidMigrationLocatorStub($this->locator);
    //     $this->migrator->setLocator($locator);

    //     // Expect a `BadClassNameException` exception
    //     $this->expectException(BadClassNameException::class);

    //     // Run up
    //     $this->migrator->run();
    // }

    // public function testDependableMigrations(): void
    // {
    //     // Change the repository so we can test with the DependableMigrationLocatorStub
    //     $locator = new DependableMigrationLocatorStub($this->locator);
    //     $this->migrator->setLocator($locator);

    //     // Run up
    //     $migrated = $this->migrator->run();
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));
    //     $this->assertTrue($this->schema->hasTable('flights'));

    //     // Note here the `two` migration has been placed at the bottom even if
    //     // it was supposed to be migrated first from the order the locator
    //     // returned them. This is because `two` migration depends on `one` migrations
    //     // We only check the last one, we don't care about the order the first two are since they are not dependent on each other
    //     $this->assertEquals('\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable', $migrated[2]);
    // }

    // TODO : This didn't worked on GH Actions... testing.dbConnection might not be working because db is initialized before.
    // https://github.com/userfrosting/sprinkle-core/runs/2925978557?check_suite_focus=true#step:11:720
    // public function testDependableMigrationsWithInstalled(): void
    // {
    //     // Run the `one` migrations
    //     $this->migrator->run();
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));

    //     // Change the repository so we can run up the `two` migrations
    //     $locator = new FlightsTableMigrationLocatorStub($this->locator);
    //     $this->migrator->setLocator($locator);

    //     // Run up again
    //     $migrated = $this->migrator->run();
    //     $this->assertTrue($this->schema->hasTable('flights'));

    //     // Only the `CreateFlightsTable` migration should be ran
    //     $this->assertEquals([
    //         '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
    //     ], $migrated);
    // }

    // public function testUnfulfillableMigrations(): void
    // {
    //     // Change the repository so we can test with the unfulfillable Stub
    //     $locator = new UnfulfillableMigrationLocatorStub($this->locator);
    //     $this->migrator->setLocator($locator);

    //     // Should have an exception for unfulfilled migrations
    //     $this->expectException(\Exception::class);
    //     $migrated = $this->migrator->run();
    // }

    // public function testSpecificMigrationCanBeRollback(): void
    // {
    //     // Change the repository so we can test with the DependableMigrationLocatorStub
    //     $locator = new DependableMigrationLocatorStub($this->locator);
    //     $this->migrator->setLocator($locator);

    //     // Run up
    //     $migrated = $this->migrator->run();
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));
    //     $this->assertTrue($this->schema->hasTable('flights'));

    //     // Rollback only the Flights table. Should work as no other depends on it
    //     $migration = '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable';
    //     $rolledBack = $this->migrator->rollbackMigration($migration);
    //     $this->assertCount(1, $rolledBack);
    //     $this->assertEquals([$migration], $rolledBack);

    //     // Look at actual db for tables. Flight should be gone, but other still there
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));
    //     $this->assertFalse($this->schema->hasTable('flights'));
    // }

    // public function testSpecificMigrationRollbackWithDependencies(): void
    // {
    //     // Change the repository so we can test with the DependableMigrationLocatorStub
    //     $locator = new DependableMigrationLocatorStub($this->locator);
    //     $this->migrator->setLocator($locator);

    //     // Run up
    //     $migrated = $this->migrator->run();
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));
    //     $this->assertTrue($this->schema->hasTable('flights'));

    //     // Rollback only the user table. Should fail as the flight table depends on it
    //     $migration = '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable';
    //     $this->expectException(\Exception::class);
    //     $rolledBack = $this->migrator->rollbackMigration($migration);

    //     // Look at actual db for tables. Should be no changes
    //     $this->assertTrue($this->schema->hasTable('users'));
    //     $this->assertTrue($this->schema->hasTable('password_resets'));
    //     $this->assertTrue($this->schema->hasTable('flights'));
    // }
}

class TestMigrateSprinkle extends Core
{
    /**
     * Replace core migration with our dumb ones.
     */
    public static function getMigrations(): array
    {
        return [
            StubMigrationA::class,
        ];
    }
}

class StubMigrationA extends Migration
{
    public function up()
    {
        $this->schema->create('test', function (Blueprint $table) {
            $table->id();
            $table->string('foo');
        });
    }

    public function down()
    {
        $this->schema->drop('test');
    }
}
