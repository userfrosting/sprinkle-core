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
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Log\DebugLogger;
use UserFrosting\Sprinkle\Core\Log\ErrorLogger;
use UserFrosting\Sprinkle\Core\Log\MailLogger;
use UserFrosting\Sprinkle\Core\Log\QueryLogger;
use UserFrosting\Sprinkle\Core\ServicesProvider\LoggersService;
use UserFrosting\Testing\ContainerStub;

/**
 * Mock tests for Loggers service.
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

        // Set mock Config
        $locator = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('logs.path')->once()->andReturn('logs://database.log')
            ->getMock();
        $this->ci->set(Config::class, $locator);
    }

    public function testDebugLogger()
    {
        $this->assertInstanceOf(Logger::class, $this->ci->get(DebugLogger::class));
        $this->assertInstanceOf(LoggerInterface::class, $this->ci->get(DebugLogger::class));
        $this->assertInstanceOf(DebugLogger::class, $this->ci->get(DebugLogger::class));
    }

    public function testErrorLogger()
    {
        $this->assertInstanceOf(Logger::class, $this->ci->get(ErrorLogger::class));
        $this->assertInstanceOf(LoggerInterface::class, $this->ci->get(ErrorLogger::class));
        $this->assertInstanceOf(ErrorLogger::class, $this->ci->get(ErrorLogger::class));
    }

    public function testMailLogger()
    {
        $this->assertInstanceOf(Logger::class, $this->ci->get(MailLogger::class));
        $this->assertInstanceOf(LoggerInterface::class, $this->ci->get(MailLogger::class));
        $this->assertInstanceOf(MailLogger::class, $this->ci->get(MailLogger::class));
    }

    public function testQueryLogger(): void
    {
        $this->assertInstanceOf(Logger::class, $this->ci->get(QueryLogger::class));
        $this->assertInstanceOf(LoggerInterface::class, $this->ci->get(QueryLogger::class));
        $this->assertInstanceOf(QueryLogger::class, $this->ci->get(QueryLogger::class));
    }
}
