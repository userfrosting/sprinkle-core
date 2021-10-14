<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database\Migrator;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * DatabaseMigrationRepository Test
 */
class DatabaseMigrationRepositoryTest extends TestCase
{
    public function testRepository(): void
    {
        $db = $this->ci->get(Capsule::class);
        $repository = new DatabaseMigrationRepository($db, 'migrationTest');

        // Create repository
        $this->assertFalse($repository->exists());
        $repository->create();
        $this->assertTrue($repository->exists());

        // Init batch number
        $this->assertSame(0, $repository->getLastBatchNumber());
        $this->assertSame(1, $repository->getNextBatchNumber());

        // Get all migrations (empty)
        $this->assertSame([], $repository->list());

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
        ], $repository->list());
        $this->assertSame(['foobar', 'barfoo'], $repository->list(2));
        $this->assertSame(['barfoo', 'foobar'], $repository->list(2, false));

        // New batch number
        $this->assertSame(4, $repository->getLastBatchNumber());
        $this->assertSame(5, $repository->getNextBatchNumber());

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

        // Delete repository
        $repository->delete();
        $this->assertFalse($repository->exists());
    }
}
