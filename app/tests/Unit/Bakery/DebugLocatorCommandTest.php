<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Bakery;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Bakery\DebugLocatorCommand;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceInterface;
use UserFrosting\UniformResourceLocator\ResourceLocationInterface;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStreamInterface;

/**
 * Test DebugLocatorCommand
 *
 * Warning: As with most bakery command testing, this test make sure all code
 * is executed and doesn't throw errors, but the actual display is not tested.
 */
class DebugLocatorCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCommand(): void
    {
        // Setup ResourceLocationInterface Mocks
        $location1 = Mockery::mock(ResourceLocationInterface::class)
            ->shouldReceive('getName')->once()->andReturn('Location 1')
            ->shouldReceive('getPath')->once()->andReturn('location/1/')
            ->getMock();
        $location2 = Mockery::mock(ResourceLocationInterface::class)
            ->shouldReceive('getName')->once()->andReturn('Location 2')
            ->shouldReceive('getPath')->once()->andReturn('location/2/')
            ->getMock();

        // Setup ResourceStreamInterface Mock
        $stream = Mockery::mock(ResourceStreamInterface::class)
            ->shouldReceive('getScheme')->once()->andReturn('config')
            ->shouldReceive('getPrefix')->once()->andReturn('')
            ->shouldReceive('getPath')->once()->andReturn('config')
            ->shouldReceive('isShared')->once()->andReturn(false)
            ->getMock();

        // Setup ResourceInterface Mocks
        $resource1 = Mockery::mock(ResourceInterface::class)
            ->shouldReceive('getAbsolutePath')->once()->andReturn('/foo/bar/locations/1/config')
            ->getMock();
        $resource2 = Mockery::mock(ResourceInterface::class)
            ->shouldReceive('getAbsolutePath')->once()->andReturn('/foo/bar/locations/2/config')
            ->getMock();

        // Setup ResourceLocatorInterface mock
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getBasePath')->once()->andReturn('/foo/bar')
            ->shouldReceive('getLocations')->once()->andReturn([$location1, $location2])
            ->shouldReceive('getStreams')->once()->andReturn(['config' => ['' => [$stream]]])
            ->shouldReceive('listStreams')->once()->andReturn(['config', 'bar'])
            ->shouldReceive('getResources')->with('config://')->once()->andReturn([$resource1, $resource2])
            ->shouldReceive('getResources')->with('bar://')->once()->andReturn([])
            ->getMock();

        // Set mock in CI and run command
        $ci = ContainerStub::create();
        $ci->set(ResourceLocatorInterface::class, $locator);

        /** @var DebugLocatorCommand */
        $command = $ci->get(DebugLocatorCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
    }
}
