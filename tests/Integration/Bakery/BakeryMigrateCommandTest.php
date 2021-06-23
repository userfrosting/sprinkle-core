<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Core\Bakery\MigrateCommand;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Testing\BakeryCommandTester;

/**
 * Test MigrateCommand
 */
class BakeryMigrateCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use TestDatabase;
    use BakeryCommandTester;

    public function testBasicMigrationsCallMigratorWithProperArguments()
    {
        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => false, 'step' => false])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Set mock in CI and run command
        $this->ci->set(Migrator::class, $migrator);
        $this->runCommand(MigrateCommand::class);
    }

    public function testMigrationRepositoryCreatedWhenNecessary()
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
        $this->ci->set(Migrator::class, $migrator);
        $this->runCommand(MigrateCommand::class);
    }

    public function testTheCommandMayBePretended()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => true, 'step' => false])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $this->ci->set(Migrator::class, $migrator);
        $this->runCommand(
            command: MigrateCommand::class,
            input: ['--pretend' => true]
        );
    }

    public function testStepMayBeSet()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('run')->once()->with(['pretend' => false, 'step' => true])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $this->ci->set(Migrator::class, $migrator);
        $this->runCommand(
            command: MigrateCommand::class,
            input: ['--step' => true]
        );
    }
}
