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
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Bakery\MigrateResetCommand;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\Testing\ContainerStub;

/**
 * MigrateResetCommand tests
 */
class MigrateResetCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testBasicMigrationsCallMigratorWithProperArguments(): void
    {
        // Setup repository mock
        $repository = m::mock(DatabaseMigrationRepository::class);
        $repository->shouldReceive('deleteRepository')->andReturn(null);

        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->twice()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('reset')->once()->with(false)->andReturn(['foo']);
        $migrator->shouldReceive('getNotes');
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateResetCommand::class);
        BakeryTester::runCommand($command);
    }

    public function testBasicCallWithNotthingToRollback(): void
    {
        // Setup repository mock
        $repository = m::mock(DatabaseMigrationRepository::class);
        $repository->shouldReceive('deleteRepository')->andReturn(null);

        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->twice()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('reset')->once()->with(false)->andReturn([]);
        $migrator->shouldReceive('getNotes');
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateResetCommand::class);
        BakeryTester::runCommand($command);
    }

    public function testTheCommandMayBePretended(): void
    {
        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRanMigrations')->once()->andReturn(['foo']);
        $migrator->shouldReceive('reset')->once()->with(true)->andReturn(['foo']);
        $migrator->shouldReceive('getNotes');
        $migrator->shouldNotReceive('getRepository');

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateResetCommand::class);
        BakeryTester::runCommand($command, ['--pretend' => true]);
    }
}
