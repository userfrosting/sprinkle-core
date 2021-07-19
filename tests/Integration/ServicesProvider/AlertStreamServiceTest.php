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
use UserFrosting\Alert\AlertStream;
use UserFrosting\Alert\CacheAlertStream;
use UserFrosting\Alert\SessionAlertStream;
use UserFrosting\I18n\Translator;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\ServicesProvider\AlertStreamService;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Testing\ContainerStub;

/**
 * Integration tests for `alerts` service.
 * Check to see if service returns what it's supposed to return
 */
class AlertStreamServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new AlertStreamService();
        $this->ci = ContainerStub::create($provider->register());
    }

    public function testCacheConfig(): void
    {
        // Set dependencies services
        $config = m::mock(Config::class);
        $config->shouldReceive('get')->with('alert.storage')->andReturn('cache');
        $config->shouldReceive('get')->with('alert.key')->andReturn('foo');
        $this->ci->set(Config::class, $config);
        $this->ci->set(Cache::class, m::mock(Cache::class));
        $this->ci->set(Translator::class, m::mock(Translator::class));
        $session = m::mock(Session::class);
        $session->shouldReceive('getId')->andReturn('foobar');
        $this->ci->set(Session::class, $session);

        // Get stream and assert the right one is returned based on config
        $this->assertInstanceOf(CacheAlertStream::class, $this->ci->get(AlertStream::class));
    }

    public function testSessionConfig(): void
    {
        // Set dependencies services
        $config = m::mock(Config::class);
        $config->shouldReceive('get')->with('alert.storage')->andReturn('session');
        $config->shouldReceive('get')->with('alert.key')->andReturn('foo');
        $this->ci->set(Config::class, $config);
        $this->ci->set(Cache::class, m::mock(Cache::class));
        $this->ci->set(Translator::class, m::mock(Translator::class));
        $this->ci->set(Session::class, m::mock(Session::class));

        // Get stream and assert the right one is returned based on config
        $this->assertInstanceOf(SessionAlertStream::class, $this->ci->get(AlertStream::class));
    }

    public function testBadConfig(): void
    {
        // Set dependencies services
        $config = m::mock(Config::class);
        $config->shouldReceive('get')->with('alert.storage')->andReturn('foo');
        $this->ci->set(Config::class, $config);
        $this->ci->set(Cache::class, m::mock(Cache::class));
        $this->ci->set(Translator::class, m::mock(Translator::class));
        $this->ci->set(Session::class, m::mock(Session::class));

        // Get stream and assert the exception is thrown.
        $this->expectException(\Exception::class);
        $this->ci->get(AlertStream::class);
    }
}
