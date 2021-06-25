<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Bakery;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Core\Bakery\MigrateRefreshCommand;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\Testing\ContainerStub;

/**
 * MigrateRefreshCommand Test
 */
class MigrateRefreshCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testBasicMigrationsCallMigratorWithProperArguments(): void
    {
        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => false, 'steps' => 1])->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => false, 'step' => false])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateRefreshCommand::class);
        BakeryTester::runCommand($command);
    }

    public function testBasicCallWithNotthingToRollback(): void
    {
        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => false, 'steps' => 1])->andReturn([]);
        $migrator->shouldNotReceive('run');
        $migrator->shouldReceive('getNotes');

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateRefreshCommand::class);
        BakeryTester::runCommand($command);
    }

    public function testStepsMayBeSet(): void
    {
        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => false, 'steps' => 3])->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => false, 'step' => false])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateRefreshCommand::class);
        BakeryTester::runCommand($command, ['--steps' => 3]);
    }
}
