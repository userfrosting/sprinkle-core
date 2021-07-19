<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use DI\Container;
use Illuminate\Cache\Repository as Cache;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
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
        $locator = m::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('findResource')->withArgs(['cache://', true, true])->andReturn('foo/');
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Set mock Config service
        $config = m::mock(Config::class);
        $config->shouldReceive('get')->with('cache.driver')->once()->andReturn('file');
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
        $this->ci->set(ResourceLocatorInterface::class, m::mock(ResourceLocatorInterface::class));

        // Set mock Config service
        $config = m::mock(Config::class);
        $config->shouldReceive('get')->with('cache.driver')->once()->andReturn('memcached');
        $config->shouldReceive('get')->with('cache.memcached')->once()->andReturn([]);
        $config->shouldReceive('get')->with('cache.prefix')->once()->andReturn('');
        $this->ci->set(Config::class, $config);

        // Get stream and assert the right one is returned based on config
        // WARNING : The service provider call MemcachedStore, but also `instance()` which return the Cache instance from MemcachedStore !!!
        //           Service should be properly tested to know MemcachedStore is called.
        $this->assertInstanceOf(Cache::class, $this->ci->get(Cache::class));
    }

    public function testRedisConfig(): void
    {
        // Set mock Locator
        $this->ci->set(ResourceLocatorInterface::class, m::mock(ResourceLocatorInterface::class));

        // Set mock Config service
        $config = m::mock(Config::class);
        $config->shouldReceive('get')->with('cache.driver')->once()->andReturn('redis');
        $config->shouldReceive('get')->with('cache.redis')->once()->andReturn([]);
        $config->shouldReceive('get')->with('cache.prefix')->once()->andReturn('');
        $this->ci->set(Config::class, $config);

        // Get stream and assert the right one is returned based on config
        // WARNING : The service provider call RedisStore, but also `instance()` which return the Cache instance from RedisStore !!!
        //           Service should be properly tested to know RedisStore is called.
        $this->assertInstanceOf(Cache::class, $this->ci->get(Cache::class));
    }

    public function testBadConfig(): void
    {
        // Set dependencies services
        $this->ci->set(ResourceLocatorInterface::class, m::mock(ResourceLocatorInterface::class));
        $config = m::mock(Config::class);
        $config->shouldReceive('get')->with('cache.driver')->times(2)->andReturn('foo');
        $this->ci->set(Config::class, $config);

        // Get stream and assert the exception is thrown.
        $this->expectException(\Exception::class);
        $this->ci->get(Cache::class);
    }
}
