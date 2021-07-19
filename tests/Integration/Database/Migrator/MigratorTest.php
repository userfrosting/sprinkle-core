<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database\Migrator;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocator;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Migrator Tests
 */
class MigratorTest extends TestCase
{
    /**
     * @var string The db connection to use for the test.
     */
    protected string $connection;

    /**
     * @var string The migration table name
     */
    protected string $migrationTable = 'migrations';

    /**
     * @var Builder
     */
    protected Builder $schema;

    /**
     * @var Migrator The migrator instance.
     */
    protected Migrator $migrator;

    /**
     * @var MigrationLocator The migration locator instance.
     */
    protected MigrationLocator $migrationLocator;

    /**
     * @var DatabaseMigrationRepository The migration repository instance.
     */
    protected DatabaseMigrationRepository $repository;

    /**
     * @var ResourceLocatorInterface The migration locator instance.
     */
    protected ResourceLocatorInterface $locator;

    /**
     * Setup migration instances used for all tests
     */
    public function setUp(): void
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Fetch services from CI
        $db = $this->ci->get(Capsule::class);
        $config = $this->ci->get(Config::class);
        $this->locator = $this->ci->get(ResourceLocatorInterface::class);

        // Set db connection name property from config
        $this->connection = $config->get('testing.dbConnection');

        // Get the repository and locator instances
        $this->repository = new DatabaseMigrationRepository($db, $this->migrationTable);
        $this->migrationLocator = new MigrationLocatorStub($this->locator);

        // Get the migrator instance and setup right connection
        $this->migrator = new Migrator($db, $this->repository, $this->migrationLocator);
        $this->migrator->setConnection($this->connection);

        // Get schema Builder
        $this->schema = $this->migrator->getSchemaBuilder();

        if (!$this->repository->repositoryExists()) {
            $this->repository->createRepository();
        }
    }

    public function testMigrationRepositoryCreated(): void
    {
        $this->assertTrue($this->schema->hasTable($this->migrationTable));
    }

    public function testBasicMigration(): void
    {
        $ran = $this->migrator->run();

        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));

        $this->assertEquals([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
        ], $ran);
    }

    // TODO : This didn't worked on GH Actions... testing.dbConnection might not be working because db is initialized before.
    // https://github.com/userfrosting/sprinkle-core/runs/2925978557?check_suite_focus=true#step:11:709
    public function testRepository(): void
    {
        $ran = $this->migrator->run();

        $expected = [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
        ];

        // Theses assertions makes sure the repository and the migration returns the same format
        // N.B.: getLast return the migrations in reverse order (last ran first)
        $this->assertEquals($expected, $ran);
        $this->assertEquals(array_reverse($expected), $this->repository->getLast());
        $this->assertEquals($expected, $this->repository->getMigrationsList());
    }

    public function testMigrationsCanBeRolledBack(): void
    {
        // Run up
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));

        $rolledBack = $this->migrator->rollback();
        $this->assertFalse($this->schema->hasTable('users'));
        $this->assertFalse($this->schema->hasTable('password_resets'));

        // Make sure the data returned from migrator is accurate.
        // N.B.: The order returned by the rollback method is ordered by which
        // migration was rolled back first (reversed from the order they where ran up)
        $this->assertEquals(array_reverse($this->migrationLocator->getMigrations()), $rolledBack);
    }

    public function testMigrationsCanBeReset(): void
    {
        // Run up
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));

        $rolledBack = $this->migrator->reset();
        $this->assertFalse($this->schema->hasTable('users'));
        $this->assertFalse($this->schema->hasTable('password_resets'));

        // Make sure the data returned from migrator is accurate.
        $this->assertEquals(array_reverse($this->migrationLocator->getMigrations()), $rolledBack);
    }

    public function testNoErrorIsThrownWhenNoOutstandingMigrationsExist(): void
    {
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->migrator->run();
    }

    public function testNoErrorIsThrownWhenNothingToRollback(): void
    {
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->migrator->rollback();
        $this->assertFalse($this->schema->hasTable('users'));
        $this->assertFalse($this->schema->hasTable('password_resets'));
        $this->migrator->rollback();
    }

    public function testPretendUp(): void
    {
        $result = $this->migrator->run(['pretend' => true]);
        $notes = $this->migrator->getNotes();
        $this->assertFalse($this->schema->hasTable('users'));
        $this->assertFalse($this->schema->hasTable('password_resets'));
        $this->assertNotEquals([], $notes);
    }

    public function testPretendRollback(): void
    {
        // Run up as usual
        $result = $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));

        $expected = [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
        ];

        $rolledBack = $this->migrator->rollback(['pretend' => true]);
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->assertEquals(array_reverse($expected), $rolledBack);
    }

    public function testWithInvalidClass(): void
    {
        // Change the repository so we can test with the InvalidMigrationLocatorStub
        $locator = new InvalidMigrationLocatorStub($this->locator);
        $this->migrator->setLocator($locator);

        // Expect a `BadClassNameException` exception
        $this->expectException(BadClassNameException::class);

        // Run up
        $this->migrator->run();
    }

    public function testDependableMigrations(): void
    {
        // Change the repository so we can test with the DependableMigrationLocatorStub
        $locator = new DependableMigrationLocatorStub($this->locator);
        $this->migrator->setLocator($locator);

        // Run up
        $migrated = $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->assertTrue($this->schema->hasTable('flights'));

        // Note here the `two` migration has been placed at the bottom even if
        // it was supposed to be migrated first from the order the locator
        // returned them. This is because `two` migration depends on `one` migrations
        // We only check the last one, we don't care about the order the first two are since they are not dependent on each other
        $this->assertEquals('\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable', $migrated[2]);
    }

    // TODO : This didn't worked on GH Actions... testing.dbConnection might not be working because db is initialized before.
    // https://github.com/userfrosting/sprinkle-core/runs/2925978557?check_suite_focus=true#step:11:720
    public function testDependableMigrationsWithInstalled(): void
    {
        // Run the `one` migrations
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));

        // Change the repository so we can run up the `two` migrations
        $locator = new FlightsTableMigrationLocatorStub($this->locator);
        $this->migrator->setLocator($locator);

        // Run up again
        $migrated = $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('flights'));

        // Only the `CreateFlightsTable` migration should be ran
        $this->assertEquals([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
        ], $migrated);
    }

    public function testUnfulfillableMigrations(): void
    {
        // Change the repository so we can test with the unfulfillable Stub
        $locator = new UnfulfillableMigrationLocatorStub($this->locator);
        $this->migrator->setLocator($locator);

        // Should have an exception for unfulfilled migrations
        $this->expectException(\Exception::class);
        $migrated = $this->migrator->run();
    }

    public function testSpecificMigrationCanBeRollback(): void
    {
        // Change the repository so we can test with the DependableMigrationLocatorStub
        $locator = new DependableMigrationLocatorStub($this->locator);
        $this->migrator->setLocator($locator);

        // Run up
        $migrated = $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->assertTrue($this->schema->hasTable('flights'));

        // Rollback only the Flights table. Should work as no other depends on it
        $migration = '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable';
        $rolledBack = $this->migrator->rollbackMigration($migration);
        $this->assertCount(1, $rolledBack);
        $this->assertEquals([$migration], $rolledBack);

        // Look at actual db for tables. Flight should be gone, but other still there
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->assertFalse($this->schema->hasTable('flights'));
    }

    public function testSpecificMigrationRollbackWithDependencies(): void
    {
        // Change the repository so we can test with the DependableMigrationLocatorStub
        $locator = new DependableMigrationLocatorStub($this->locator);
        $this->migrator->setLocator($locator);

        // Run up
        $migrated = $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->assertTrue($this->schema->hasTable('flights'));

        // Rollback only the user table. Should fail as the flight table depends on it
        $migration = '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable';
        $this->expectException(\Exception::class);
        $rolledBack = $this->migrator->rollbackMigration($migration);

        // Look at actual db for tables. Should be no changes
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->assertTrue($this->schema->hasTable('flights'));
    }
}

class MigrationLocatorStub extends MigrationLocator
{
    public function getMigrations(): array
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
        ];
    }
}

class FlightsTableMigrationLocatorStub extends MigrationLocator
{
    public function getMigrations(): array
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
        ];
    }
}

/**
 *    This stub contain migration which file doesn't exists
 */
class InvalidMigrationLocatorStub extends MigrationLocator
{
    public function getMigrations(): array
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\Foo',
        ];
    }
}

/**
 *    This stub contain migration which order they need to be run is different
 *    than the order the file are returned because of dependencies management.
 *    The `two` migration should be run last since it depends on the other two
 */
class DependableMigrationLocatorStub extends MigrationLocator
{
    public function getMigrations(): array
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
        ];
    }
}

/**
 *    This stub contain migration which order they need to be run is different
 *    than the order the file are returned because of dependencies management
 */
class UnfulfillableMigrationLocatorStub extends MigrationLocator
{
    public function getMigrations(): array
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\UnfulfillableTable',
        ];
    }
}
