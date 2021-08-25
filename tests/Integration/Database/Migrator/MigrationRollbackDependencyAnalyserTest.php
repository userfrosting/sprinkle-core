<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database\Migrator;

use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRollbackDependencyAnalyser;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationRollbackException;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Testing\TestCase;

class MigrationRollbackDependencyAnalyserTest extends TestCase
{
    use TestDatabase;

    protected string $mainSprinkle = TestRollbackMigrationSprinkle::class;

    protected MigrationRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();

        // Add installed
        /** @var MigrationRepositoryInterface */
        $this->repository = $this->ci->get(MigrationRepositoryInterface::class);
        $this->repository->log(StubAnalyserRollbackMigrationA::class, 1);
        $this->repository->log(StubAnalyserRollbackMigrationC::class, 2);
        $this->repository->log(StubAnalyserRollbackMigrationB::class, 2);
        $this->repository->log(StubAnalyserRollbackMigrationD::class, 3);
    }

    public function testConstruct(): MigrationRollbackDependencyAnalyser
    {
        $analyser = $this->ci->get(MigrationRollbackDependencyAnalyser::class);
        $this->assertInstanceOf(MigrationRollbackDependencyAnalyser::class, $analyser);

        return $analyser;
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationRollbackDependencyAnalyser $analyser
     */
    public function testCanRollbackMigration(MigrationRollbackDependencyAnalyser $analyser): void
    {
        // "D" and "A" are clear for rollback
        $this->assertTrue($analyser->canRollbackMigration(StubAnalyserRollbackMigrationD::class));
        $this->assertTrue($analyser->canRollbackMigration(StubAnalyserRollbackMigrationA::class));

        // B can be removed, as it depend on "C", but none depend on it
        $this->assertTrue($analyser->canRollbackMigration(StubAnalyserRollbackMigrationB::class));

        // But "C" can't be removed, as "B" depends on it and it's still installed
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationC::class));

        // "E" is not installed, so can't rollback
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationE::class));
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationRollbackDependencyAnalyser $analyser
     */
    public function testGetMigrationsForRollbackForLast(MigrationRollbackDependencyAnalyser $analyser): void
    {
        $resultA = $analyser->getMigrationsForRollback();
        $resultB = $analyser->getMigrationsForRollback(1);

        $this->assertSame($resultA, $resultB);
        $this->assertSame([StubAnalyserRollbackMigrationD::class], $resultA);
        $this->assertSame([StubAnalyserRollbackMigrationD::class], $resultB);
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationRollbackDependencyAnalyser $analyser
     */
    public function testGetMigrationsForRollbackForStep2(MigrationRollbackDependencyAnalyser $analyser): void
    {
        $result = $analyser->getMigrationsForRollback(2);

        $this->assertSame([
            StubAnalyserRollbackMigrationD::class,
            StubAnalyserRollbackMigrationB::class,
            StubAnalyserRollbackMigrationC::class,
        ], $result);
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationRollbackDependencyAnalyser $analyser
     */
    public function testGetMigrationsForReset(MigrationRollbackDependencyAnalyser $analyser): void
    {
        $result = $analyser->getMigrationsForReset();

        $this->assertSame([
            StubAnalyserRollbackMigrationD::class,
            StubAnalyserRollbackMigrationB::class,
            StubAnalyserRollbackMigrationC::class,
            StubAnalyserRollbackMigrationA::class,
        ], $result);
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationRollbackDependencyAnalyser $analyser
     */
    public function testGetMigrationsForRollbackForTooManyStep(MigrationRollbackDependencyAnalyser $analyser): void
    {
        // Will do same as reset in this case
        $result = $analyser->getMigrationsForRollback(99);

        $this->assertSame([
            StubAnalyserRollbackMigrationD::class,
            StubAnalyserRollbackMigrationB::class,
            StubAnalyserRollbackMigrationC::class,
            StubAnalyserRollbackMigrationA::class,
        ], $result);
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationRollbackDependencyAnalyser $analyser
     */
    public function testGetMigrationsForRollbackForStaleError(MigrationRollbackDependencyAnalyser $analyser): void
    {
        // Add "E" as installed. It's gonna be stale
        $this->repository->log(StubAnalyserRollbackMigrationE::class, 1);

        // Get analyser back to propagate changes
        $analyser = $this->ci->get(MigrationRollbackDependencyAnalyser::class);

        // Expect exception because of stale migration.
        $this->expectException(MigrationRollbackException::class);
        $analyser->getMigrationsForRollback(1);
    }
}

class TestRollbackMigrationSprinkle extends Core
{
    /**
     * Replace core migration with our dumb ones.
     */
    public static function getMigrations(): array
    {
        return [
            StubAnalyserRollbackMigrationA::class,
            StubAnalyserRollbackMigrationB::class,
            StubAnalyserRollbackMigrationC::class,
            StubAnalyserRollbackMigrationD::class,
        ];
    }
}

class StubAnalyserRollbackMigrationA implements MigrationInterface
{
    public function up()
    {
    }

    public function down()
    {
    }
}

class StubAnalyserRollbackMigrationB extends StubAnalyserRollbackMigrationA
{
    public static $dependencies = [
        StubAnalyserRollbackMigrationC::class,
    ];
}

class StubAnalyserRollbackMigrationC extends StubAnalyserRollbackMigrationA
{
}

class StubAnalyserRollbackMigrationD extends StubAnalyserRollbackMigrationA
{
}

class StubAnalyserRollbackMigrationE extends StubAnalyserRollbackMigrationA
{
}
