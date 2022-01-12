<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Migrator;

use DI\Container;
use Illuminate\Database\Schema\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\SprinkleMigrationLocator;
use UserFrosting\Sprinkle\Core\ServicesProvider\MigratorService;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\Core\Tests\Integration\TestSprinkle;
use UserFrosting\Sprinkle\RecipeExtensionLoader;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Support\Exception\ClassNotFoundException;
use UserFrosting\Testing\ContainerStub;

/**
 * Migration Locator Tests
 */
class SprinkleMigrationLocatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testConstruct(): SprinkleMigrationLocator
    {
        $builder = Mockery::mock(Builder::class);

        $ci = Mockery::mock(Container::class)
            ->shouldReceive('get')->with(StubMigrationA::class)->andReturn(new StubMigrationA($builder))
            ->shouldReceive('get')->with(StubMigrationB::class)->andReturn(new StubMigrationB($builder))
            ->getMock();

        /** @var SprinkleManager */
        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([new MigrationsSprinkleStub()])
            ->getMock();

        $loader = new RecipeExtensionLoader($manager, $ci);
        $locator = new SprinkleMigrationLocator($loader);

        return $locator;
    }

    /**
     * @depends testConstruct
     */
    public function testGetAll(SprinkleMigrationLocator $locator): void
    {
        $migrations = $locator->all();

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
            ->shouldReceive('getSprinkles')->andReturn([new MigrationsSprinkleStub()])
            ->getMock();
        $ci->set(SprinkleManager::class, $sprinkleManager);

        /** @var MigrationLocatorInterface */
        $locator = $ci->get(MigrationLocatorInterface::class);

        // Get all migrations assertions
        $migrations = $locator->all();
        $this->assertCount(2, $migrations);
        $this->assertInstanceOf(StubMigrationA::class, $migrations[0]);
        $this->assertInstanceOf(StubMigrationB::class, $migrations[1]);
    }

    /**
     * @depends testConstruct
     * @depends testGetAll
     */
    public function testList(SprinkleMigrationLocator $locator): void
    {
        $this->assertSame([
            StubMigrationA::class,
            StubMigrationB::class,
        ], $locator->list());
    }

    /**
     * @depends testConstruct
     * @depends testList
     */
    public function testHas(SprinkleMigrationLocator $locator): void
    {
        $this->assertTrue($locator->has(StubMigrationA::class));
        $this->assertFalse($locator->has(StubMigrationC::class)); // @phpstan-ignore-line
    }

    /**
     * @depends testConstruct
     * @depends testHas
     */
    public function testGet(SprinkleMigrationLocator $locator): void
    {
        $migration = $locator->get(StubMigrationA::class);
        $this->assertInstanceOf(StubMigrationA::class, $migration);
    }

    /**
     * @depends testConstruct
     * @depends testHas
     */
    public function testGetWithNotFound(SprinkleMigrationLocator $locator): void
    {
        $this->expectException(ClassNotFoundException::class);
        $locator->get(StubMigrationC::class); // @phpstan-ignore-line
    }
}

class StubMigrationA extends Migration
{
    public function up(): void
    {
    }

    public function down(): void
    {
    }
}

class StubMigrationB extends Migration
{
    public function up(): void
    {
    }

    public function down(): void
    {
    }
}

class MigrationsSprinkleStub extends TestSprinkle implements MigrationRecipe
{
    public function getMigrations(): array
    {
        return [
            StubMigrationA::class,
            StubMigrationB::class,
        ];
    }
}
