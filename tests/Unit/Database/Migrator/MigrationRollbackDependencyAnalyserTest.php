<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Migrator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRollbackDependencyAnalyser;

class MigrationRollbackDependencyAnalyserTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testConstruct(): MigrationRollbackDependencyAnalyser
    {
        $installed = Mockery::mock(MigrationRepositoryInterface::class)
            // ->shouldReceive('getMigrationsList')->andReturn([
            //     StubAnalyserRollbackMigrationA::class,
            //     StubAnalyserRollbackMigrationB::class,
            //     StubAnalyserRollbackMigrationC::class,
            //     StubAnalyserRollbackMigrationD::class,
            // ])
            ->shouldReceive('hasMigration')->with(StubAnalyserRollbackMigrationA::class)->andReturn(true)
            ->shouldReceive('hasMigration')->with(StubAnalyserRollbackMigrationB::class)->andReturn(true)
            ->shouldReceive('hasMigration')->with(StubAnalyserRollbackMigrationC::class)->andReturn(true)
            ->shouldReceive('hasMigration')->with(StubAnalyserRollbackMigrationD::class)->andReturn(true)
            ->shouldReceive('hasMigration')->with(StubAnalyserRollbackMigrationE::class)->andReturn(false)
            ->getMock();

        $available = Mockery::mock(MigrationLocatorInterface::class)
            ->shouldReceive('list')->andReturn([
                StubAnalyserRollbackMigrationB::class,
                StubAnalyserRollbackMigrationC::class,
                StubAnalyserRollbackMigrationD::class,
            ])
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationA::class)->andReturn(false)
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationB::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationC::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationD::class)->andReturn(true)
            ->shouldReceive('has')->with(StubAnalyserRollbackMigrationE::class)->andReturn(false)
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationB::class)->andReturn(new StubAnalyserRollbackMigrationB())
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationC::class)->andReturn(new StubAnalyserRollbackMigrationC())
            ->shouldReceive('get')->with(StubAnalyserRollbackMigrationD::class)->andReturn(new StubAnalyserRollbackMigrationD())
            ->getMock();

        $analyser = new MigrationRollbackDependencyAnalyser($installed, $available);

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
        // "D" is clear for rollback
        $this->assertTrue($analyser->canRollbackMigration(StubAnalyserRollbackMigrationD::class));

        // B can be removed, as it depend on "C", but none depend on it
        $this->assertTrue($analyser->canRollbackMigration(StubAnalyserRollbackMigrationB::class));

        // But "C" can't be removed, as "B" depends on it and it's still installed
        // TODO
        // $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationC::class));

        // "A" is stale, so can't rollback
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationA::class));

        // "E" is not installed, so can't rollback
        $this->assertFalse($analyser->canRollbackMigration(StubAnalyserRollbackMigrationE::class));
    }

    /**
     * @depends testConstruct
     *
     * @param MigrationRollbackDependencyAnalyser $analyser
     */
    // TODO
    /*public function testGetMigrationsForResetWithFailure(MigrationRollbackDependencyAnalyser $analyser): void
    {
        // Will fail as A is stale
        $this->expectException(\Exception::class); // TODO
        $analyser->getMigrationsForReset();
    }*/

    /**
     * @depends testConstruct
     *
     * @param MigrationRollbackDependencyAnalyser $analyser
     */
    // public function testGetMigrationsForReset(MigrationRollbackDependencyAnalyser $analyser): void
    // {
    //     // Force "A" to be available
    //     // TODO

    //     $result = $analyser->getMigrationsForReset();

    //     $this->assertSame([
    //         StubAnalyserRollbackMigrationD::class,
    //         StubAnalyserRollbackMigrationB::class,
    //         StubAnalyserRollbackMigrationC::class,
    //         StubAnalyserRollbackMigrationA::class,
    //     ], $result);
    // }
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
