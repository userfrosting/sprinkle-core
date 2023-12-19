<?php

declare(strict_types=1);

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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Log\DebugLogger;
use UserFrosting\Sprinkle\Core\Log\DebugLoggerInterface;
use UserFrosting\Sprinkle\Core\Log\ErrorLogger;
use UserFrosting\Sprinkle\Core\Log\ErrorLoggerInterface;
use UserFrosting\Sprinkle\Core\Log\MailLogger;
use UserFrosting\Sprinkle\Core\Log\MailLoggerInterface;
use UserFrosting\Sprinkle\Core\Log\QueryLogger;
use UserFrosting\Sprinkle\Core\Log\QueryLoggerInterface;
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
            ->shouldReceive('getString')->with('logs.path')->once()->andReturn('logs://database.log')
            ->getMock();
        $this->ci->set(Config::class, $locator);
    }

    /**
     * @param class-string $interface
     * @param class-string $class
     *
     * @dataProvider loggerProvider
     */
    public function testLogger(string $interface, string $class): void
    {
        $object = $this->ci->get($interface);
        $this->assertInstanceOf($class, $object);
        $this->assertInstanceOf(LoggerInterface::class, $object);
        $this->assertInstanceOf($interface, $object);
    }

    /**
     * @return array<array<class-string, class-string>>
     */
    public static function loggerProvider(): array
    {
        return [
            [DebugLoggerInterface::class, DebugLogger::class],
            [ErrorLoggerInterface::class, ErrorLogger::class],
            [MailLoggerInterface::class, MailLogger::class],
            [QueryLoggerInterface::class, QueryLogger::class],
        ];
    }
}
