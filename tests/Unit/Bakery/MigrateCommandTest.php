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
use UserFrosting\Sprinkle\Core\Bakery\MigrateCommand;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use PHPUnit\Framework\TestCase;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\Testing\ContainerStub;

/**
 * Test MigrateCommand
 */
class MigrateCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testBasicMigrationsCallMigratorWithProperArguments(): void
    {
        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => false, 'step' => false])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Set mock in CI and run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateCommand::class);
        BakeryTester::runCommand($command);
    }

    public function testMigrationRepositoryCreatedWhenNecessary(): void
    {
        $migrator = m::mock(Migrator::class);
        $repository = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository');

        $migrator->shouldReceive('repositoryExists')->once()->andReturn(false);
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => false, 'step' => false])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        $repository->shouldReceive('createRepository')->once();

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateCommand::class);
        BakeryTester::runCommand($command);
    }

    public function testTheCommandMayBePretended(): void
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => true, 'step' => false])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateCommand::class);
        BakeryTester::runCommand(
            command: $command,
            input: ['--pretend' => true]
        );
    }

    public function testStepMayBeSet(): void
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => false, 'step' => true])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateCommand::class);
        BakeryTester::runCommand(
            command: $command,
            input: ['--step' => true]
        );
    }
}
