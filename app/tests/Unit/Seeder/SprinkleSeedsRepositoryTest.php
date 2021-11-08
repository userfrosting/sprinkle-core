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
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Exceptions\BadInstanceOfException;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;
use UserFrosting\Sprinkle\Core\Seeder\SeedRepositoryInterface;
use UserFrosting\Sprinkle\Core\Seeder\SprinkleSeedsRepository;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe;
use UserFrosting\Sprinkle\Core\Tests\Integration\TestSprinkle;
use UserFrosting\Sprinkle\Core\Util\ClassRepository\ClassRepositoryInterface;
use UserFrosting\Sprinkle\RecipeExtensionLoader;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Support\Exception\NotFoundException;

/**
 * SprinkleSeedsRepository Test
 */
class SprinkleSeedsRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testConstruct(): SeedRepositoryInterface
    {
        $ci = Mockery::mock(Container::class)
            ->shouldReceive('get')->with(StubSeedA::class)->andReturn(new StubSeedA())
            ->shouldReceive('get')->with(StubSeedB::class)->andReturn(new StubSeedB())
            ->getMock();

        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([SeedsSprinkleStub::class])
            ->getMock();

        $loader = new RecipeExtensionLoader($manager, $ci);
        $repository = new SprinkleSeedsRepository($loader);

        $this->assertInstanceOf(SeedRepositoryInterface::class, $repository);
        $this->assertInstanceOf(ClassRepositoryInterface::class, $repository);

        return $repository;
    }

    /**
     * @depends testConstruct
     */
    public function testGetAll(SprinkleSeedsRepository $repository): void
    {
        $seeds = $repository->all();

        $this->assertIsArray($seeds);
        $this->assertCount(2, $seeds);
        $this->assertInstanceOf(StubSeedA::class, $seeds[0]);
        $this->assertInstanceOf(StubSeedB::class, $seeds[1]);
    }

    /**
     * @depends testGetAll
     */
    public function testGetAllWithBadSeed(): void
    {
        $ci = Mockery::mock(Container::class)
            ->shouldReceive('get')->with(StubSeedA::class)->andReturn(new StubSeedA())
            ->shouldReceive('get')->with(StubSeedB::class)->andReturn(new StubSeedB())
            ->getMock();

        $manager = Mockery::mock(SprinkleManager::class)
            ->shouldReceive('getSprinkles')->andReturn([BadSeedsSprinkleStub::class])
            ->getMock();

        $loader = new RecipeExtensionLoader($manager, $ci);
        $repository = new SprinkleSeedsRepository($loader);

        $this->assertInstanceOf(SeedRepositoryInterface::class, $repository);
        $this->assertInstanceOf(ClassRepositoryInterface::class, $repository);

        // Set expectations
        $this->expectException(BadInstanceOfException::class);
        $this->expectExceptionMessage('Class `' . StubNotSeed::class . '` must be instance of ' . SeedInterface::class);

        // Perform
        $repository->all();
    }

    /**
     * @depends testConstruct
     * @depends testGetAll
     */
    public function testList(SprinkleSeedsRepository $repository): void
    {
        $this->assertSame([
            StubSeedA::class,
            StubSeedB::class,
        ], $repository->list());
    }

    /**
     * @depends testConstruct
     * @depends testList
     */
    public function testHas(SprinkleSeedsRepository $repository): void
    {
        $this->assertTrue($repository->has(StubSeedA::class));
        $this->assertFalse($repository->has(StubSeedC::class));
    }

    /**
     * @depends testConstruct
     * @depends testHas
     */
    public function testGet(SprinkleSeedsRepository $repository): void
    {
        $migration = $repository->get(StubSeedA::class);
        $this->assertInstanceOf(StubSeedA::class, $migration);
    }

    /**
     * @depends testConstruct
     * @depends testHas
     */
    public function testGetWithNotFound(SprinkleSeedsRepository $repository): void
    {
        $this->expectException(NotFoundException::class);
        $repository->get(StubSeedC::class);
    }
}

class StubSeedA implements SeedInterface
{
    public function run(): void
    {
    }
}

class StubSeedB implements SeedInterface
{
    public function run(): void
    {
    }
}

class StubNotSeed
{
    public function run(): void
    {
    }
}

class SeedsSprinkleStub extends TestSprinkle implements SeedRecipe
{
    public static function getSeeds(): array
    {
        return [
            StubSeedA::class,
            StubSeedB::class,
        ];
    }
}

class BadSeedsSprinkleStub extends TestSprinkle implements SeedRecipe
{
    public static function getSeeds(): array
    {
        return [
            StubSeedA::class,
            StubSeedB::class,
            StubNotSeed::class,
        ];
    }
}
