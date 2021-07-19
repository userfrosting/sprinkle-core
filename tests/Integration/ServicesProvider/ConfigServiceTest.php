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
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\ServicesProvider\ConfigService;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Integration tests for `config` service.
 * Check to see if service returns what it's supposed to return
 */
class ConfigServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new ConfigService();
        $this->ci = ContainerStub::create($provider->register());

        // Set mock Locator
        $locator = m::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('getBasePath')->andReturn('');
        $this->ci->set(ResourceLocatorInterface::class, $locator);
    }

    // TODO : Requires Service to be reworked with more injection
    /*public function testService()
    {
        $this->assertInstanceOf(Config::class, $this->ci->get(Config::class));
    }*/
}
