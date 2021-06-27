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
use Mockery as m;
use UserFrosting\Support\Repository\Repository as Config;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Slim\Views\Twig;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\ServicesProvider\TwigService;
use UserFrosting\Sprinkle\Core\Twig\CoreExtension;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Integration tests for `view` service.
 * Check to see if service returns what it's supposed to return
 */
class ViewServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new TwigService();
        $this->ci = ContainerStub::create($provider->register());

        // Set dependencies services
        $this->ci->set(CoreExtension::class, m::mock(CoreExtension::class));
    }
    
    public function testService()
    {
        // Set dependencies services
        $config = m::mock(Config::class);
        $config->shouldReceive('get')->with('cache.twig')->once()->andReturn(false);
        $config->shouldReceive('get')->with('debug.twig')->once()->andReturn(false);
        $this->ci->set(Config::class, $config);

        $locator = m::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('getResources')->andReturn([]);
        $this->ci->set(ResourceLocatorInterface::class, $locator);
        
        $this->assertInstanceOf(Twig::class, $this->ci->get(Twig::class));
    }

    /**
     * @depends testService
     */
    public function testServiceWithCacheAndDebug()
    {
        // Set dependencies services
        $config = m::mock(Config::class);
        $config->shouldReceive('get')->with('cache.twig')->once()->andReturn(true);
        $config->shouldReceive('get')->with('debug.twig')->once()->andReturn(true);
        $this->ci->set(Config::class, $config);

        $locator = m::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('getResources')->once()->andReturn([]);
        $locator->shouldReceive('findResource')->with('cache://twig', true, true)->once()->andReturn('');
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        $this->assertInstanceOf(Twig::class, $this->ci->get(Twig::class));
    }
}
