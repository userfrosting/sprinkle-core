<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database\Migrator;

use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationDependencyNotMetException;
use UserFrosting\Testing\TestCase;

/**
 * Sub test for Migrator.
 * Tests dependencies management related methods.
 */
class MigratorDependencyTest extends TestCase
{
    protected string $mainSprinkle = TestMigrationSprinkle::class;

    protected MigrationRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        // Add installed
        /** @var MigrationRepositoryInterface */
        $this->repository = $this->ci->get(MigrationRepositoryInterface::class);
        $this->repository->log(StubAnalyserMigrationA::class, 1);
        $this->repository->log(StubAnalyserMigrationD::class, 2);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->repository->delete();
    }

    public function testConstruct(): void
    {
        $migrator = $this->ci->get(Migrator::class);
        $this->assertInstanceOf(Migrator::class, $migrator);
    }

    public function testGetInstalled(): void
    {
        $migrator = $this->ci->get(Migrator::class);

        $this->assertSame([
            StubAnalyserMigrationA::class,
            StubAnalyserMigrationD::class,
        ], $migrator->getInstalled());
    }

    public function testGetAvailable(): void
    {
        $migrator = $this->ci->get(Migrator::class);

        $this->assertSame([
            StubAnalyserMigrationA::class,
            StubAnalyserMigrationB::class,
            StubAnalyserMigrationC::class,
            StubAnalyserMigrationE::class,
        ], $migrator->getAvailable());
    }

    public function testGetPending(): void
    {
        $migrator = $this->ci->get(Migrator::class);

        $this->assertSame([
            StubAnalyserMigrationC::class, // C is before B because B depend on C
            StubAnalyserMigrationB::class,
            StubAnalyserMigrationE::class, // Will be installed since D is installed even if not available.
        ], $migrator->getPending());
    }

    public function testGetStale(): void
    {
        $migrator = $this->ci->get(Migrator::class);

        $this->assertSame([
            StubAnalyserMigrationD::class, // Installed, not available
        ], $migrator->getStale());
    }

    /**
     * @depends testGetInstalled
     */
    public function testGetPendingWithUnmatchedDependencies(): void
    {
        // Remove D from installed, then E will fail
        $this->repository->remove(StubAnalyserMigrationD::class);

        // Get migrator
        $migrator = $this->ci->get(Migrator::class);

        // Make sure installed is right
        $this->assertSame([
            StubAnalyserMigrationA::class,
        ], $migrator->getInstalled());

        // Set exception expectation
        $this->expectException(MigrationDependencyNotMetException::class);
        $this->expectExceptionMessage(StubAnalyserMigrationE::class . ' depends on ' . StubAnalyserMigrationD::class . ", but it's not available.");

        // Get pending
        $migrator->getPending();
    }
}

class TestMigrationSprinkle extends Core
{
    /**
     * Replace core migration with our dumb ones.
     */
    public function getMigrations(): array
    {
        return [
            StubAnalyserMigrationA::class,
            StubAnalyserMigrationB::class,
            StubAnalyserMigrationC::class,
            StubAnalyserMigrationE::class,
        ];
    }
}

class StubAnalyserMigrationA implements MigrationInterface
{
    public function up(): void
    {
    }

    public function down(): void
    {
    }
}

class StubAnalyserMigrationB extends StubAnalyserMigrationA
{
    public static $dependencies = [
        StubAnalyserMigrationC::class,
    ];
}

class StubAnalyserMigrationC extends StubAnalyserMigrationA
{
}

class StubAnalyserMigrationE extends StubAnalyserMigrationA
{
    public static $dependencies = [
        StubAnalyserMigrationD::class, // D doesn't exist on purpose, but it IS installed
    ];
}
