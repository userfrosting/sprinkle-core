<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Bakery\MigrateRollbackCommand;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\Testing\ContainerStub;

/**
 * MigrateRollbackCommand
 */
class MigrateRollbackCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testBasicMigrationsCallMigratorWithProperArguments(): void
    {
        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => false, 'steps' => 1])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateRollbackCommand::class);
        BakeryTester::runCommand($command);
    }

    public function testMigrationRepositoryCreatedWhenNecessary(): void
    {
        $migrator = m::mock(Migrator::class);
        $repository = m::mock(DatabaseMigrationRepository::class);

        $migrator->shouldReceive('repositoryExists')->once()->andReturn(false);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => false, 'steps' => 1])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        $repository->shouldReceive('create')->once();

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateRollbackCommand::class);
        BakeryTester::runCommand($command);
    }

    public function testTheCommandMayBePretended(): void
    {
        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => true, 'steps' => 1])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateRollbackCommand::class);
        BakeryTester::runCommand($command, ['--pretend' => true]);
    }

    public function testStepsMayBeSet(): void
    {
        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => false, 'steps' => 3])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateRollbackCommand::class);
        BakeryTester::runCommand($command, ['--steps' => 3]);
    }
}
