<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Migrator;

use Illuminate\Database\Connection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationDependencyNotMetException;

/**
 * Sub test for Migrator.
 * Tests dependencies management related methods.
 */
class MigratorDependencyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testConstruct(): Migrator
    {
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserMigrationA::class,
                StubAnalyserMigrationD::class,
            ])
            ->getMock();

        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserMigrationA::class,
                StubAnalyserMigrationB::class,
                StubAnalyserMigrationC::class,
                StubAnalyserMigrationE::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserMigrationA::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationB::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationC::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationD::class)->andReturn(false)
            ->shouldReceive('has')->with(StubAnalyserMigrationE::class)->andReturn(true)
            ->shouldReceive('get')->with(StubAnalyserMigrationA::class)->andReturn(new StubAnalyserMigrationA())
            ->shouldReceive('get')->with(StubAnalyserMigrationB::class)->andReturn(new StubAnalyserMigrationB())
            ->shouldReceive('get')->with(StubAnalyserMigrationC::class)->andReturn(new StubAnalyserMigrationC())
            ->shouldReceive('get')->with(StubAnalyserMigrationE::class)->andReturn(new StubAnalyserMigrationE())
            ->getMock();

        $connection = Mockery::mock(Connection::class);

        $analyser = new Migrator($installed, $available, $connection);

        $this->assertInstanceOf(Migrator::class, $analyser);

        return $analyser;
    }

    /**
     * @depends testConstruct
     *
     * @param Migrator $analyser
     */
    public function testGetInstalled(Migrator $analyser): void
    {
        $this->assertSame([
            StubAnalyserMigrationA::class,
            StubAnalyserMigrationD::class,
        ], $analyser->getInstalled());
    }

    /**
     * @depends testConstruct
     *
     * @param Migrator $analyser
     */
    public function testGetAvailable(Migrator $analyser): void
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
     * @depends testGetInstalled
     * @depends testGetAvailable
     *
     * @param Migrator $analyser
     */
    public function testGetPending(Migrator $analyser): void
    {
        $this->assertSame([
            StubAnalyserMigrationC::class, // C is before B because B depend on C
            StubAnalyserMigrationB::class,
            StubAnalyserMigrationE::class,
        ], $analyser->getPending());
    }

    /**
     * @depends testConstruct
     * @depends testGetInstalled
     * @depends testGetAvailable
     *
     * @param Migrator $analyser
     */
    public function testGetStale(Migrator $analyser): void
    {
        $this->assertSame([
            StubAnalyserMigrationD::class,
        ], $analyser->getStale());
    }

    /**
     * @depends testGetPending
     */
    public function testGetPendingThirdStageDependency(): void
    {
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserMigrationA::class,
                StubAnalyserMigrationD::class,
            ])
            ->getMock();

        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserMigrationH::class, // Place H first
                StubAnalyserMigrationA::class,
                StubAnalyserMigrationB::class,
                StubAnalyserMigrationC::class,
                StubAnalyserMigrationE::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserMigrationA::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationB::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationC::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserMigrationD::class)->andReturn(false)
            ->shouldReceive('has')->with(StubAnalyserMigrationE::class)->andReturn(true)
            ->shouldReceive('get')->with(StubAnalyserMigrationA::class)->andReturn(new StubAnalyserMigrationA())
            ->shouldReceive('get')->with(StubAnalyserMigrationB::class)->andReturn(new StubAnalyserMigrationB())
            ->shouldReceive('get')->with(StubAnalyserMigrationC::class)->andReturn(new StubAnalyserMigrationC())
            ->shouldReceive('get')->with(StubAnalyserMigrationE::class)->andReturn(new StubAnalyserMigrationE())
            ->shouldReceive('get')->with(StubAnalyserMigrationH::class)->andReturn(new StubAnalyserMigrationH())
            ->getMock();

        $connection = Mockery::mock(Connection::class);

        $analyser = new Migrator($installed, $available, $connection);

        $this->assertSame([
            StubAnalyserMigrationC::class, // C is before B because B depend on C
            StubAnalyserMigrationB::class,
            StubAnalyserMigrationH::class, // H should be first, but it require B, that require C.
            StubAnalyserMigrationE::class,
        ], $analyser->getPending());
    }

    /**
     * @depends testGetPending
     */
    public function testGetPendingWithNonAvailable(): void
    {
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->andReturn([])
            ->getMock();

        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserMigrationG::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserMigrationF::class)->andReturn(false)
            ->shouldReceive('get')->with(StubAnalyserMigrationG::class)->andReturn(new StubAnalyserMigrationG())
            ->getMock();

        $connection = Mockery::mock(Connection::class);

        $analyser = new Migrator($installed, $available, $connection);

        $this->expectException(MigrationDependencyNotMetException::class);
        $this->expectExceptionMessage(StubAnalyserMigrationG::class . ' depends on ' . StubAnalyserMigrationF::class . ", but it's not available.");

        $analyser->getPending();
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

class StubAnalyserMigrationG extends StubAnalyserMigrationA
{
    public static $dependencies = [
        StubAnalyserMigrationF::class, // F doesn't exist on purpose, and it's NOT installed
    ];
}

class StubAnalyserMigrationH extends StubAnalyserMigrationA
{
    public static $dependencies = [
        StubAnalyserMigrationB::class,
    ];
}
