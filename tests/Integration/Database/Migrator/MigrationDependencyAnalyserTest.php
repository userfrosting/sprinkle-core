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
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationDependencyAnalyser;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationDependencyNotMetException;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Testing\TestCase;

class MigrationDependencyAnalyserTest extends TestCase
{
    use TestDatabase;

    protected string $mainSprinkle = TestMigrationSprinkle::class;

    protected MigrationRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();

        // Add installed
        /** @var MigrationRepositoryInterface */
        $this->repository = $this->ci->get(MigrationRepositoryInterface::class);
        $this->repository->log(StubAnalyserMigrationA::class, 1);
        $this->repository->log(StubAnalyserMigrationD::class, 2);
    }

    public function testConstruct(): MigrationDependencyAnalyser
    {
        $analyser = $this->ci->get(MigrationDependencyAnalyser::class);
        $this->assertInstanceOf(MigrationDependencyAnalyser::class, $analyser);

        return $analyser;
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationDependencyAnalyser $analyser
     */
    public function testGetInstalled(MigrationDependencyAnalyser $analyser): void
    {
        $this->assertSame([
            StubAnalyserMigrationA::class,
            StubAnalyserMigrationD::class,
        ], $analyser->getInstalled());
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationDependencyAnalyser $analyser
     */
    public function testGetAvailable(MigrationDependencyAnalyser $analyser): void
    {
        $this->assertSame([
            StubAnalyserMigrationA::class,
            StubAnalyserMigrationB::class,
            StubAnalyserMigrationC::class,
            StubAnalyserMigrationE::class,
        ], $analyser->getAvailable());
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationDependencyAnalyser $analyser
     */
    public function testGetPending(MigrationDependencyAnalyser $analyser): void
    {
        $this->assertSame([
            StubAnalyserMigrationC::class, // C is before B because B depend on C
            StubAnalyserMigrationB::class,
            StubAnalyserMigrationE::class, // Will be installed since D is installed even if not available.
        ], $analyser->getPending());
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationDependencyAnalyser $analyser
     */
    public function testGetStale(MigrationDependencyAnalyser $analyser): void
    {
        $this->assertSame([
            StubAnalyserMigrationD::class, // Installed, not available
        ], $analyser->getStale());
    }

    /**
     * @depends testGetInstalled
     */
    public function testGetPendingWithUnmatchedDependencies(): void
    {
        // Remove D from installed, then E will fail
        $this->repository->remove(StubAnalyserMigrationD::class);

        // Get analyser back
        $analyser = $this->ci->get(MigrationDependencyAnalyser::class);

        // Make sure installed is right
        $this->assertSame([
            StubAnalyserMigrationA::class,
        ], $analyser->getInstalled());

        // Set exception expectation
        $this->expectException(MigrationDependencyNotMetException::class);
        $this->expectExceptionMessage(StubAnalyserMigrationE::class . ' depends on ' . StubAnalyserMigrationD::class . ", but it's not available.");

        // Get pending
        $analyser->getPending();
    }
}

class TestMigrationSprinkle extends Core
{
    /**
     * Replace core migration with our dumb ones.
     */
    public static function getMigrations(): array
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
    public function up()
    {
    }

    public function down()
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
