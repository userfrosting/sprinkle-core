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
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocator;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\ServicesProvider\MigratorService;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\RecipeExtensionLoader;
use UserFrosting\Testing\ContainerStub;

/**
 * Migration Locator Tests
 */
class MigrationLocatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @dataProvider migrationDataProvider
     */
    public function testGetMigrations(array $migrations): void
    {
        $manager = Mockery::mock(RecipeExtensionLoader::class)
                ->shouldReceive('getInstances')
                ->with('getMigrations', MigrationRecipe::class, MigrationInterface::class)
                ->once()
                ->andReturn($migrations)
                ->getMock();

        $locator = new MigrationLocator($manager);

        $this->assertInstanceOf(MigrationLocatorInterface::class, $locator);
        $this->assertSame($migrations, $locator->getMigrations());
    }

    /**
     * @dataProvider migrationDataProvider
     */
    public function testGetMigrationsService(array $migrations): void
    {
        // Create container with provider to test
        $provider = new MigratorService();
        $ci = ContainerStub::create($provider->register());

        $manager = Mockery::mock(RecipeExtensionLoader::class)
                ->shouldReceive('getInstances')
                ->with('getMigrations', MigrationRecipe::class, MigrationInterface::class)
                ->once()
                ->andReturn($migrations)
                ->getMock();
        $ci->set(RecipeExtensionLoader::class, $manager);

        /** @var MigrationLocatorInterface */
        $locator = $ci->get(MigrationLocatorInterface::class);

        $this->assertInstanceOf(MigrationLocatorInterface::class, $locator);
        $this->assertSame($migrations, $locator->getMigrations());
    }

    public function migrationDataProvider(): array
    {
        return [
            [[]],
            [[StubMigrationA::class, StubMigrationB::class]],
        ];
    }
}

class StubMigrationA extends Migration
{
    public function up()
    {
    }

    public function down()
    {
    }
}

class StubMigrationB extends Migration
{
    public function up()
    {
    }

    public function down()
    {
    }
}
