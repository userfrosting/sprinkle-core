<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\ServicesProvider;

use DI\Container;
use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\ServicesProvider\CacheService;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Integration tests for `cache` service.
 * Check to see if service returns what it's supposed to return
 */
class CacheServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new CacheService();
        $this->ci = ContainerStub::create($provider->register());
    }

    public function testFileConfig(): void
    {
        // Set mock Locator
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->withArgs(['cache://', true, true])->andReturn('foo/')
            ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('cache.driver')->once()->andReturn('file')
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Get stream and assert the right one is returned based on config
        // WARNING : The service provider call TaggableFileStore, but also `instance()` which return the Cache instance from TaggableFileStore !!!
        //           Service should be properly tested to know TaggableFileStore is called.
        $this->assertInstanceOf(Cache::class, $this->ci->get(Cache::class));
    }

    /**
     * @requires extension Memcached
     */
    // TODO : Mock MemcachedStore so the extension is no longer a requirement for this test
    public function testMemcachedConfig(): void
    {
        // Set mock Locator
        $this->ci->set(ResourceLocatorInterface::class, Mockery::mock(ResourceLocatorInterface::class));

        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('cache.driver')->once()->andReturn('memcached')
            ->shouldReceive('get')->with('cache.memcached')->once()->andReturn([])
            ->shouldReceive('get')->with('cache.prefix')->once()->andReturn('')
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Get stream and assert the right one is returned based on config
        // WARNING : The service provider call MemcachedStore, but also `instance()` which return the Cache instance from MemcachedStore !!!
        //           Service should be properly tested to know MemcachedStore is called.
        $this->assertInstanceOf(Cache::class, $this->ci->get(Cache::class));
    }

    public function testRedisConfig(): void
    {
        // Set mock Locator
        $this->ci->set(ResourceLocatorInterface::class, Mockery::mock(ResourceLocatorInterface::class));

        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('cache.driver')->once()->andReturn('redis')
            ->shouldReceive('get')->with('cache.redis')->once()->andReturn([])
            ->shouldReceive('get')->with('cache.prefix')->once()->andReturn('')
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Get stream and assert the right one is returned based on config
        // WARNING : The service provider call RedisStore, but also `instance()` which return the Cache instance from RedisStore !!!
        //           Service should be properly tested to know RedisStore is called.
        $this->assertInstanceOf(Cache::class, $this->ci->get(Cache::class));
    }

    public function testBadConfig(): void
    {
        // Set mock Locator
        $this->ci->set(ResourceLocatorInterface::class, Mockery::mock(ResourceLocatorInterface::class));

        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('cache.driver')->times(2)->andReturn('foo')
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Get stream and assert the exception is thrown.
        $this->expectException(\Exception::class);
        $this->ci->get(Cache::class);
    }
}
