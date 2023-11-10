<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database\Migrator;

use Illuminate\Database\Schema\Builder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Database\Models\MigrationTable;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationNotFoundException;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * DatabaseMigrationRepository Test
 */
class DatabaseMigrationRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRepositoryCreation(): void
    {
        // Repository should not exists before migration is instantiated
        /** @var Builder */
        $builder = $this->ci->get(Builder::class);
        $this->assertFalse($builder->hasTable('migrationTest'));

        // Replace the default model with a custom one for testing
        $this->ci->set(MigrationTable::class, new TestMigration());
        $repository = $this->ci->get(DatabaseMigrationRepository::class);

        // Table should exist
        $this->assertTrue($builder->hasTable('migrationTest'));
        $this->assertTrue($repository->exists());

        // Check table structure
        $this->assertSame([
            'id',
            'migration',
            'batch',
        ], $builder->getColumnListing('migrationTest'));

        // Delete repository
        $repository->delete();
        $this->assertFalse($builder->hasTable('migrationTest'));
        $this->assertFalse($repository->exists());
    }

    public function testRepository(): void
    {
        $this->ci->set(MigrationTable::class, new TestMigration());
        $repository = $this->ci->get(DatabaseMigrationRepository::class);

        // Init batch number
        $this->assertSame(0, $repository->getLastBatchNumber());
        $this->assertSame(1, $repository->getNextBatchNumber());

        // Get all migrations (empty)
        $this->assertSame([], $repository->list());

        // Insert 2 from the same batch, plus two other batch
        $repository->log('foo', 2);
        $repository->log('bar', 2);
        $repository->log('foobar', 3);
        $repository->log('barfoo');

        // Get Lists
        $this->assertSame([
            'foo',
            'bar',
            'foobar',
            'barfoo',
        ], $repository->list());
        $this->assertSame(['foobar', 'barfoo'], $repository->list(2));
        $this->assertSame(['barfoo', 'foobar'], $repository->list(2, false));

        // New batch number
        $this->assertSame(4, $repository->getLastBatchNumber());
        $this->assertSame(5, $repository->getNextBatchNumber());

        // Test Last
        $this->assertSame(['barfoo'], $repository->last());

        // Get single Migration
        $this->assertTrue($repository->has('foobar'));
        $migration = $repository->get('foobar');
        $this->assertSame('foobar', $migration->migration);
        $this->assertSame(3, (int) $migration->batch);

        // Delete Migration
        $repository->remove('foobar');
        $this->assertSame([
            'foo',
            'bar',
            'barfoo',
        ], $repository->list());
    }

    public function testMigrationNotFound(): void
    {
        $this->ci->set(MigrationTable::class, new TestMigration());
        $repository = $this->ci->get(DatabaseMigrationRepository::class);

        $this->expectException(MigrationNotFoundException::class);
        $repository->get('foo');
    }

    /**
     * Legacy support for old migration class name.
     * UF V4 stored the migrations with a leading slash, which was removed in
     * UF V5, since we now use `::Class` to get the class name, instead of an
     * hardcoded string.
     * @see : https://github.com/userfrosting/UserFrosting/blob/adb574f378fb0af1c5eaa3be71458869431e7410/app/sprinkles/core/src/Database/Migrator/MigrationLocator.php#L86
     */
    public function testLegacyMigrations(): void
    {
        // Replace the default model with a custom one for testing
        $this->ci->set(MigrationTable::class, new TestMigration());
        $repository = $this->ci->get(DatabaseMigrationRepository::class);

        // Log the migration with the old class name ("\" at the beginning)
        $repository->log('\\' . MigrationClassStub::class);

        // Check if the migration exists, should be, for legacy support. Accept both standard
        $this->assertSame([MigrationClassStub::class], $repository->list());
        $this->assertTrue($repository->has(MigrationClassStub::class));
        $this->assertTrue($repository->has('\\' . MigrationClassStub::class));
        $this->assertSame(MigrationClassStub::class, $repository->all()[0]->migration);

        // Test with new format
        $result = $repository->get(MigrationClassStub::class);
        $this->assertInstanceOf(TestMigration::class, $result);
        $this->assertSame(MigrationClassStub::class, $result->migration);

        // Test get with legacy format
        $result = $repository->get('\\' . MigrationClassStub::class);
        $this->assertSame(MigrationClassStub::class, $result->migration);

        // Test last
        $this->assertSame([MigrationClassStub::class], $repository->last());

        // Delete
        $repository->remove(MigrationClassStub::class);
        $this->assertFalse($repository->has(MigrationClassStub::class));
    }
}

// N.B.: Stub doesn't need to be a real migration class, just needs to be a class that exists
class MigrationClassStub
{
}

class TestMigration extends MigrationTable
{
    protected $table = 'migrationTest';
}
