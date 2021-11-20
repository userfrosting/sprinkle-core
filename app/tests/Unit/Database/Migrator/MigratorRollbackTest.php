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
use UserFrosting\Sprinkle\Core\Exceptions\MigrationRollbackException;

/**
 * Sub test for Migrator.
 * Tests rollback related methods.
 */
class MigratorRollbackTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Test base functionality
     */
    public function testGetMigrationsForRollback(): void
    {
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->once()->andReturn(true)
            ->getMock();

        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->once()->andReturn([
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationD::class)->once()->andReturn(new StubAnalyserRollbackMigrationD())
            ->getMock();

        $connection = Mockery::mock(Connection::class);

        $analyser = new Migrator($installed, $available, $connection);

        $result = $analyser->getMigrationsForRollback(1);
        $this->assertSame([StubAnalyserRollbackMigrationD::class], $result);
    }

    /**
     * Test base functionality (reset version).
     */
    public function testGetMigrationsForReset(): void
    {
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->once()->andReturn(true)
            ->getMock();

        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->once()->andReturn([
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationD::class)->once()->andReturn(new StubAnalyserRollbackMigrationD())
            ->getMock();

        $connection = Mockery::mock(Connection::class);

        $analyser = new Migrator($installed, $available, $connection);

        $result = $analyser->getMigrationsForReset();
        $this->assertSame([StubAnalyserRollbackMigrationD::class], $result);
    }

    /**
     * @depends testGetMigrationsForRollback
     * @depends testGetMigrationsForReset
     */
    public function testValidateRollbackMigration(): void
    {
        // Set mock & analyser
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->times(4)->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->twice()->andReturn(true)
            ->getMock();

        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(true)
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationA::class)->twice()->andReturn(new StubAnalyserRollbackMigrationA())
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationB::class)->twice()->andReturn(new StubAnalyserRollbackMigrationB())
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationC::class)->times(4)->andReturn(new StubAnalyserRollbackMigrationC()) // Once for main, one as a dependency
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationD::class)->twice()->andReturn(new StubAnalyserRollbackMigrationD())
            ->getMock();

        $connection = Mockery::mock(Connection::class);

        $analyser = new Migrator($installed, $available, $connection);

        // Run command
        $this->assertNull($analyser->validateRollbackMigration(StubAnalyserRollbackMigrationD::class));
        $this->assertTrue($analyser->canRollbackMigration(StubAnalyserRollbackMigrationD::class));
    }

    /**
     * @depends testGetMigrationsForRollback
     * @depends testGetMigrationsForReset
     */
    public function testValidateRollbackMigrationForNotInstalledException(): void
    {
        // Set mock & analyser
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->twice()->andReturn(false)
            ->getMock();
        $available = Mockery::mock(MigrationLocatorInterface::class);
        $connection = Mockery::mock(Connection::class);
        $analyser = new Migrator($installed, $available, $connection);

        // Start by testing false
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationD::class));

        // Set exception expectation
        $this->expectException(MigrationRollbackException::class);
        $this->expectExceptionMessage('Migration is not installed : ' . StubAnalyserRollbackMigrationD::class);

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationD::class);
    }

    /**
     * @depends testGetMigrationsForRollback
     * @depends testGetMigrationsForReset
     */
    public function testValidateRollbackMigrationForStaleException(): void
    {
        // Set mock & analyser
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->twice()->andReturn(true)
            ->getMock();
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->getMock();
        $connection = Mockery::mock(Connection::class);
        $analyser = new Migrator($installed, $available, $connection);

        // Start by testing false
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationD::class));

        // Set exception expectation
        $this->expectException(MigrationRollbackException::class);
        $this->expectExceptionMessage('Stale migration detected : ' . StubAnalyserRollbackMigrationA::class . ', ' . StubAnalyserRollbackMigrationB::class);

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationD::class);
    }

    /**
     * @depends testValidateRollbackMigration
     */
    public function testValidateRollbackMigrationForDependenciesNotMet(): void
    {
        // Set mock & analyser
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->times(4)->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(true)
            ->getMock();
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(true)
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationA::class)->twice()->andReturn(new StubAnalyserRollbackMigrationA())
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationB::class)->twice()->andReturn(new StubAnalyserRollbackMigrationB())
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(new StubAnalyserRollbackMigrationC())
            ->getMock();
        $connection = Mockery::mock(Connection::class);
        $analyser = new Migrator($installed, $available, $connection);

        // Run command
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationC::class));

        // Set exception expectation
        $this->expectException(MigrationRollbackException::class);
        $this->expectExceptionMessage(StubAnalyserRollbackMigrationC::class . ' cannot be rolled back since ' . StubAnalyserRollbackMigrationB::class . ' depends on it.');

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationC::class);
    }

    /**
     * @depends testValidateRollbackMigrationForDependenciesNotMet
     *
     * N.B.: That that it fails because "B" depends on "C" which is still not installed or available.
     * This is a very edge case, as "B" shouldn't be installed if "C" is not available.
     * "A" is used as intermediate, to simulate error in second level dependencies.
     */
    public function testValidateRollbackMigrationForDependenciesDoesntExist(): void
    {
        // Set mock & analyser
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->times(4)->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationA::class)->twice()->andReturn(true)
            ->getMock();
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserRollbackMigrationA::class,
                StubAnalyserRollbackMigrationB::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(false)
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationA::class)->twice()->andReturn(new StubAnalyserRollbackMigrationA())
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationB::class)->twice()->andReturn(new StubAnalyserRollbackMigrationB())
            ->getMock();
        $connection = Mockery::mock(Connection::class);
        $analyser = new Migrator($installed, $available, $connection);

        // Run command
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationA::class));

        // Set exception expectation
        $this->expectException(MigrationRollbackException::class);
        $this->expectExceptionMessage(StubAnalyserRollbackMigrationB::class . ' depends on ' . StubAnalyserRollbackMigrationC::class . ", but it's not available.");

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationA::class);
    }

    /**
     * @depends testValidateRollbackMigrationForDependenciesNotMet
     *
     * N.B.: Same as previous test, but without "A" as the intermediate.
     * Should also fails, as "C" is still not installed or there.
     */
    public function testValidateRollbackMigrationForDependenciesDoesntExistWithDirectMigration(): void
    {
        // Set mock & analyser
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            ->shouldReceive('list')->times(4)->andReturn([
                StubAnalyserRollbackMigrationB::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationB::class)->twice()->andReturn(true)
            ->getMock();
        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->twice()->andReturn([
                StubAnalyserRollbackMigrationB::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->twice()->andReturn(false)
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationB::class)->twice()->andReturn(new StubAnalyserRollbackMigrationB())
            ->getMock();
        $connection = Mockery::mock(Connection::class);
        $analyser = new Migrator($installed, $available, $connection);

        // Run command
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationB::class));

        // Set exception expectation
        $this->expectException(MigrationRollbackException::class);
        $this->expectExceptionMessage(StubAnalyserRollbackMigrationB::class . ' depends on ' . StubAnalyserRollbackMigrationC::class . ", but it's not available.");

        // Run command
        $analyser->validateRollbackMigration(StubAnalyserRollbackMigrationB::class);
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