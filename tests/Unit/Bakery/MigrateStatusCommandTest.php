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
use UserFrosting\Sprinkle\Core\Bakery\MigrateStatusCommand;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\Testing\ContainerStub;

/**
 * MigrateStatusCommand
 */
class MigrateStatusCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testBasicMigrationsCallMigratorWithProperArguments(): void
    {
        // Define dummy data
        $available = ['foo', 'bar', 'oof', 'rab'];
        // $installed = $this->getInstalledMigrationStub()->pluck('migration')->all();
        $pending = ['oof', 'rab'];
        
        // Setup repository mock
        $repository = m::mock(DatabaseMigrationRepository::class);
        $repository->shouldReceive('getMigrations')->once()->andReturn($this->getInstalledMigrationStub());

        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $migrator->shouldReceive('setConnection')->once()->with(null)->andReturn(null);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);
        $migrator->shouldReceive('getAvailableMigrations')->once()->andReturn($available);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn($pending);


        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $ci->set(DatabaseMigrationRepository::class, $repository);
        $command = $ci->get(MigrateStatusCommand::class);
        BakeryTester::runCommand($command);
    }

    public function testDatabaseMayBeSet(): void
    {
        // Setup migrator mock
        $migrator = m::mock(Migrator::class);
        $repository = m::mock(DatabaseMigrationRepository::class);

        // Define dummy data
        $available = ['foo', 'bar', 'oof', 'rab'];
        $installed = $this->getInstalledMigrationStub()->pluck('migration')->all();
        $pending = ['oof', 'rab'];

        // Set expectations
        $migrator->shouldReceive('setConnection')->once()->with('test')->andReturn(null);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);
        $migrator->shouldReceive('getAvailableMigrations')->once()->andReturn($available);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn($pending);

        $repository->shouldReceive('getMigrations')->once()->andReturn($this->getInstalledMigrationStub());

        // Run command
        $ci = ContainerStub::create();
        $ci->set(Migrator::class, $migrator);
        $command = $ci->get(MigrateStatusCommand::class);
        BakeryTester::runCommand($command, ['--database' => 'test']);
    }

    protected function getInstalledMigrationStub()
    {
        return collect([
            (object) ['migration' => 'foo', 'batch' => 1, 'sprinkle' => 'foo'],
            (object) ['migration' => 'bar', 'batch' => 2, 'sprinkle' => 'bar'],
        ]);
    }
}
