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
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;

/**
 * DatabaseMigrationRepository Test
 */
class DatabaseMigrationRepositoryTest extends TestCase
{
    use TestDatabase;

    public function testRepository(): void
    {
        // Setup test database
        $this->setupTestDatabase();

        $db = $this->ci->get(Capsule::class);
        $repository = new DatabaseMigrationRepository($db, 'migrationTest');

        // Create repository
        $this->assertFalse($repository->repositoryExists());
        $repository->createRepository();
        $this->assertTrue($repository->repositoryExists());

        // Init batch number
        $this->assertSame(0, $repository->getLastBatchNumber());
        $this->assertSame(1, $repository->getNextBatchNumber());

        // Get all migrations (empty)
        $this->assertSame([], $repository->getMigrationsList());

        // Insert 2 from the same batch, plus tow other batch
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
        ], $repository->getMigrationsList());
        $this->assertSame(['foobar', 'barfoo'], $repository->getMigrationsList(2));
        $this->assertSame(['barfoo', 'foobar'], $repository->getMigrationsList(2, false));

        // New batch number
        $this->assertSame(4, $repository->getLastBatchNumber());
        $this->assertSame(5, $repository->getNextBatchNumber());

        // Get single Migration
        $this->assertTrue($repository->hasMigration('foobar'));
        $migration = $repository->getMigration('foobar');
        $this->assertSame('foobar', $migration->migration);
        $this->assertSame('3', $migration->batch);

        // Delete Migration
        $repository->delete('foobar');
        $this->assertSame([
            'foo',
            'bar',
            'barfoo',
        ], $repository->getMigrationsList());

        // Delete repository
        $repository->deleteRepository();
        $this->assertFalse($repository->repositoryExists());
    }
}
