<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database\Migrator;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Testing\TestCase;

/**
 * Migrator Tests
 */
class MigratorTest extends TestCase
{
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
        // N.B.: Requires to get schema from connection, as otherwise it might
        // not work (different :memory: instance)
        $schema = $migrator->getConnection()->getSchemaBuilder();

        // Initial state, table doesn't exist.
        $this->assertFalse($schema->hasTable('test'));

        // Pretend to migrate
        $result = $migrator->pretendToMigrate();

        // Assert results
        // N.B.: Don't assert exact string here, because it could change depending
        //       of DB, we only assert structure for now.
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertIsString($result[StubMigrationA::class][0]['query']);

        // Table still doesn't exist.
        $this->assertFalse($schema->hasTable('test'));
    }

    /**
     * @depends testConstructor
     */
    public function testMigrate(Migrator $migrator): void
    {
        // N.B.: Requires to get schema from connection, as otherwise it might
        // not work (different :memory: instance)
        $schema = $migrator->getConnection()->getSchemaBuilder();

        // Initial state, table doesn't exist.
        $this->assertFalse($schema->hasTable('test'));

        // Migrate
        $result = $migrator->migrate();

        // Assert results
        $this->assertSame([StubMigrationA::class], $result);

        // Assert table has been created
        $this->assertTrue($schema->hasTable('test'));
    }

    /**
     * @depends testConstructor
     * @depends testMigrate
     */
    public function testMigrateWithNoOutstanding(Migrator $migrator): void
    {
        $result = $migrator->migrate();
        $this->assertSame([], $result);
    }

    /**
     * @depends testConstructor
     * @depends testMigrate
     *
     * N.B.: Depends on testMigrate, so `StubMigrationA` is installed.
     */
    public function testPretendToRollback(Migrator $migrator): void
    {
        // N.B.: Requires to get schema from connection, as otherwise it might
        // not work (different :memory: instance)
        $schema = $migrator->getConnection()->getSchemaBuilder();

        // Initial state, table exist.
        $this->assertTrue($schema->hasTable('test'));

        // Pretend to rollback
        $result = $migrator->pretendToRollback();

        // Assert results
        // N.B.: Don't assert exact string here, because it could change depending
        //       of DB, we only assert structure for now.
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertIsString($result[StubMigrationA::class][0]['query']);

        // Table stills exist.
        $this->assertTrue($schema->hasTable('test'));
    }

    /**
     * @depends testConstructor
     * @depends testMigrate
     *
     * N.B.: Depends on testMigrate, so `StubMigrationA` is installed.
     */
    public function testRollback(Migrator $migrator): void
    {
        // N.B.: Requires to get schema from connection, as otherwise it might
        // not work (different :memory: instance)
        $schema = $migrator->getConnection()->getSchemaBuilder();

        // Initial state, table exist.
        $this->assertTrue($schema->hasTable('test'));

        // Rollback
        $result = $migrator->rollback();

        // Assert results
        $this->assertSame([StubMigrationA::class], $result);

        // Assert table has been removed
        $this->assertFalse($schema->hasTable('test'));
    }

    /**
     * @depends testConstructor
     * @depends testRollback
     */
    public function testRollbackWithNoOutstanding(Migrator $migrator): void
    {
        $result = $migrator->rollback();
        $this->assertSame([], $result);
    }

    /**
     * @depends testConstructor
     */
    public function testReset(Migrator $migrator): void
    {
        // Test it's empty
        $result = $migrator->pretendToReset();
        $this->assertSame([], $result);

        // Install migration
        $result = $migrator->migrate();
        $this->assertSame([StubMigrationA::class], $result);
        $schema = $migrator->getConnection()->getSchemaBuilder();
        $this->assertTrue($schema->hasTable('test'));

        // Test pretend to reset
        $result = $migrator->pretendToReset();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertIsString($result[StubMigrationA::class][0]['query']);

        // Test reset
        $result = $migrator->reset();
        $this->assertSame([StubMigrationA::class], $result);
        $schema = $migrator->getConnection()->getSchemaBuilder();
        $this->assertFalse($schema->hasTable('test'));
    }
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
