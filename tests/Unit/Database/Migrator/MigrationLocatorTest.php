<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Migrator;

use DI\Container;
use Illuminate\Database\Schema\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocator;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\ServicesProvider\MigratorService;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\Core\Tests\Integration\TestSprinkle;
use UserFrosting\Sprinkle\RecipeExtensionLoader;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Testing\ContainerStub;

/**
 * Migration Locator Tests
 */
class MigrationLocatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testConstruct(): MigrationLocator
    {
        $builder = Mockery::mock(Builder::class);

        $ci = Mockery::mock(Container::class)
            ->shouldReceive('get')->with(StubMigrationA::class)->andReturn(new StubMigrationA($builder))
            ->shouldReceive('get')->with(StubMigrationB::class)->andReturn(new StubMigrationB($builder))
            ->getMock();

        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([MigrationsSprinkleStub::class])
            ->getMock();

        $loader = new RecipeExtensionLoader($manager, $ci);
        $locator = new MigrationLocator($loader);

        $this->assertInstanceOf(MigrationLocatorInterface::class, $locator);

        return $locator;
    }

    /**
     * @depends testConstruct
     */
    public function testGetAll(MigrationLocator $locator): void
    {
        $migrations = $locator->getAll();

        $this->assertIsArray($migrations);
        $this->assertCount(2, $migrations);
        $this->assertInstanceOf(StubMigrationA::class, $migrations[0]);
        $this->assertInstanceOf(StubMigrationB::class, $migrations[1]);
    }

    /**
     * @depends testConstruct
     * @depends testGetAll
     */
    public function testConstructAndGetAllThroughService(): void
    {
        // Create container with provider to test
        $provider = new MigratorService();
        $ci = ContainerStub::create($provider->register());

        // Mock builder for migration creation
        $builder = Mockery::mock(Builder::class);
        $ci->set(Builder::class, $builder);

        // Mock Sprinkle Manager so it return the Migration stub
        $sprinkleManager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([MigrationsSprinkleStub::class])
            ->getMock();
        $ci->set(SprinkleManager::class, $sprinkleManager);

        /** @var MigrationLocatorInterface */
        $locator = $ci->get(MigrationLocatorInterface::class);

        $this->assertInstanceOf(MigrationLocatorInterface::class, $locator);

        // Get all migrations assertions
        $migrations = $locator->getAll();
        $this->assertIsArray($migrations);
        $this->assertCount(2, $migrations);
        $this->assertInstanceOf(StubMigrationA::class, $migrations[0]);
        $this->assertInstanceOf(StubMigrationB::class, $migrations[1]);
    }

    /**
     * @depends testConstruct
     * @depends testGetAll
     */
    public function testHas(MigrationLocator $locator): void
    {
        $this->assertTrue($locator->has(StubMigrationA::class));
        $this->assertFalse($locator->has(StubMigrationC::class));
    }

    /**
     * @depends testConstruct
     * @depends testHas
     */
    public function testGet(MigrationLocator $locator): void
    {
        $migration = $locator->get(StubMigrationA::class);
        $this->assertInstanceOf(StubMigrationA::class, $migration);
    }

    /**
     * @depends testConstruct
     * @depends testHas
     */
    public function testGetWithNotFound(MigrationLocator $locator): void
    {
        $this->expectException(NotFoundException::class);
        $locator->get(StubMigrationC::class);
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

class MigrationsSprinkleStub extends TestSprinkle implements MigrationRecipe
{
    public static function getMigrations(): array
    {
        return [
            StubMigrationA::class,
            StubMigrationB::class,
        ];
    }
}
