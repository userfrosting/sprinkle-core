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
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\ServicesProvider\LoggersService;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Integration tests for Loggers service.
 * Check to see if service returns what it's supposed to return
 */
class LoggersServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new LoggersService();
        $this->ci = ContainerStub::create($provider->register());

        // Set mock Locator
        $locator = m::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('findResource')->withArgs(['log://userfrosting.log', true, true])->andReturn('foo/');
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // TODO : Main service requires more injections. Once this is done, better mocking is required to properly test each features.
    }

    public function testDebugLogger()
    {
        $this->assertInstanceOf(Logger::class, $this->ci->get('debugLogger'));
    }

    public function testErrorLogger()
    {
        $this->assertInstanceOf(Logger::class, $this->ci->get('errorLogger'));
    }

    public function testMailLogger()
    {
        $this->assertInstanceOf(Logger::class, $this->ci->get('mailLogger'));
    }

    public function testQueryLogger()
    {
        $this->assertInstanceOf(Logger::class, $this->ci->get('queryLogger'));
    }
}
