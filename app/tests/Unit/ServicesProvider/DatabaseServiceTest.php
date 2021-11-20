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
use Illuminate\Database\Capsule\Manager as Capsule;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Listeners\LogExecutedQuery;
use UserFrosting\Sprinkle\Core\Log\QueryLogger;
use UserFrosting\Sprinkle\Core\ServicesProvider\DatabaseService;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Testing\ContainerStub;

/**
 * Integration tests for `debugLogger` service.
 * Check to see if service returns what it's supposed to return
 */
class DatabaseServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new DatabaseService();
        $this->ci = ContainerStub::create($provider->register());
    }

    public function testBuilder(): void
    {
        // Define mock config
        $data = [
            'memory' => [
                'driver'   => 'sqlite',
                'database' => ':memory:',
            ]
        ];

        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('db.connections')->once()->andReturn($data)
            ->shouldReceive('get')->with('db.default')->once()->andReturn('memory')
            ->shouldReceive('get')->with('debug.queries')->once()->andReturn(false)
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Set mock QueryLogger
        $this->ci->set(QueryLogger::class, Mockery::mock(QueryLogger::class));

        // Get service
        $this->assertInstanceOf(Capsule::class, $this->ci->get(Capsule::class));
    }

    public function testBuilderWithQueryLogger(): void
    {
        // Define mock config
        $data = [
            'memory' => [
                'driver'   => 'sqlite',
                'database' => ':memory:',
            ]
        ];

        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('db.connections')->once()->andReturn($data)
            ->shouldReceive('get')->with('db.default')->once()->andReturn('memory')
            ->shouldReceive('get')->with('debug.queries')->once()->andReturn(true)
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Set mock QueryLogger
        $logger = Mockery::mock(LogExecutedQuery::class);
        $this->ci->set(LogExecutedQuery::class, $logger);

        // Get service
        $this->assertInstanceOf(Capsule::class, $this->ci->get(Capsule::class));
    }
}