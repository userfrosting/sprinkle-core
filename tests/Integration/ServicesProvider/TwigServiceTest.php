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
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Sprinkle\Core\ServicesProvider\TwigService;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\Core\Tests\Integration\TestSprinkle;
use UserFrosting\Sprinkle\Core\Twig\Extensions\TwigAlertsExtension;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceInterface;
use UserFrosting\UniformResourceLocator\ResourceLocationInterface;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Integration tests for `view` service.
 * Check to see if service returns what it's supposed to return
 */
class TwigServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new TwigService();
        $this->ci = ContainerStub::create($provider->register());
    }

    public function testService()
    {
        // Set Config Mock
        $config = Mockery::mock(Config::class)
                ->shouldReceive('get')->with('cache.twig')->once()->andReturn(false)
                ->shouldReceive('get')->with('debug.twig')->once()->andReturn(false)
                ->getMock();
        $this->ci->set(Config::class, $config);

        // Set Locator Mock
        $locator = Mockery::mock(ResourceLocatorInterface::class)
                ->shouldReceive('getResources')->once()->andReturn([])
                ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Set SprinkleManager Mock
        $manager = Mockery::mock(SprinkleManager::class)
                ->shouldReceive('getSprinkles')->once()->andReturn([])
                ->getMock();
        $this->ci->set(SprinkleManager::class, $manager);

        // Assert Service is returned.
        $this->assertInstanceOf(Twig::class, $this->ci->get(Twig::class));
    }

    /**
     * @depends testService
     */
    public function testServiceWithCacheAndDebug()
    {
        // Set Config Mock
        $config = Mockery::mock(Config::class)
                ->shouldReceive('get')->with('cache.twig')->once()->andReturn(true)
                ->shouldReceive('get')->with('debug.twig')->once()->andReturn(true)
                ->getMock();
        $this->ci->set(Config::class, $config);

        // Set Locator Mock
        $locator = Mockery::mock(ResourceLocatorInterface::class)
                ->shouldReceive('getResources')->once()->andReturn([])
                ->shouldReceive('findResource')->with('cache://twig', true, true)->once()->andReturn('')
                ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Set SprinkleManager Mock
        $manager = Mockery::mock(SprinkleManager::class)
                ->shouldReceive('getSprinkles')->once()->andReturn([])
                ->getMock();
        $this->ci->set(SprinkleManager::class, $manager);

        $this->assertInstanceOf(Twig::class, $this->ci->get(Twig::class));
    }

    /**
     * Test The extension will be loaded and executed
     *
     * @depends testService
     */
    public function testServiceWithExtensions()
    {
        // Set Config Mock
        $config = Mockery::mock(Config::class)
                ->shouldReceive('get')->with('cache.twig')->once()->andReturn(false)
                ->shouldReceive('get')->with('debug.twig')->once()->andReturn(false)
                ->getMock();
        $this->ci->set(Config::class, $config);

        // Set Locator Mock
        $locator = Mockery::mock(ResourceLocatorInterface::class)
                ->shouldReceive('getResources')->once()->andReturn([])
                ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Set SprinkleManager Mock
        $manager = Mockery::mock(SprinkleManager::class)
                ->shouldReceive('getSprinkles')->once()->andReturn([SprinkleStub::class])
                ->getMock();
        $this->ci->set(SprinkleManager::class, $manager);

        $results = [
            ['message' => 'foo'],
            ['message' => 'bar'],
        ];

        // Define mock AlertStream
        $alertStream = Mockery::mock(AlertStream::class)
                    ->shouldReceive('getAndClearMessages')
                    ->once()
                    ->andReturn($results)
                    ->getMock();
        $this->ci->set(AlertStream::class, $alertStream);

        // Assert Service is returned.
        $view = $this->ci->get(Twig::class);
        $this->assertInstanceOf(Twig::class, $view);

        // Assert
        $result = $view->fetchFromString('{% for alert in getAlerts() %}{{alert.message}}{% endfor %}');
        $this->assertSame('foobar', $result);
    }

    /**
     * Test Cache and Debug is called. Also test addPath is called when resources.
     * 
     * @depends testServiceWithExtensions
     */
    public function testServiceWithExtensionsNotTwigRecipe()
    {
        // Set Config Mock
        $config = Mockery::mock(Config::class)
                ->shouldReceive('get')->with('cache.twig')->once()->andReturn(false)
                ->shouldReceive('get')->with('debug.twig')->once()->andReturn(false)
                ->getMock();
        $this->ci->set(Config::class, $config);

        // Set Mock Resource
        $location = Mockery::mock(ResourceLocationInterface::class)
                ->shouldReceive('getName')->once()->andReturn('foobar')
                ->getMock();
        $resource = Mockery::mock(ResourceInterface::class)
                ->shouldReceive('__toString')->once()->andReturn('')
                ->shouldReceive('getAbsolutePath')->once()->andReturn('')
                ->shouldReceive('getLocation')->once()->andReturn($location)
                ->getMock();

        // Set Locator Mock
        $locator = Mockery::mock(ResourceLocatorInterface::class)
                ->shouldReceive('getResources')->once()->andReturn([$resource])
                ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Set SprinkleManager Mock
        $manager = Mockery::mock(SprinkleManager::class)
                ->shouldReceive('getSprinkles')->once()->andReturn([SprinkleNotTwigExtensionStub::class])
                ->getMock();
        $this->ci->set(SprinkleManager::class, $manager);

        // Assert Service is returned.
        $view = $this->ci->get(Twig::class);
        $this->assertInstanceOf(Twig::class, $view);

        // Assert
        $this->expectException(\Twig\Error\SyntaxError::class);
        $view->fetchFromString('{% for alert in getAlerts() %}{{alert.message}}{% endfor %}');
    }

    /**
     * @depends testServiceWithExtensions
     */
    public function testServiceWithClassNotExist()
    {
        // Set Config Mock
        $config = Mockery::mock(Config::class)
                ->shouldReceive('get')->with('cache.twig')->once()->andReturn(false)
                ->shouldReceive('get')->with('debug.twig')->once()->andReturn(false)
                ->getMock();
        $this->ci->set(Config::class, $config);

        // Set Locator Mock
        $locator = Mockery::mock(ResourceLocatorInterface::class)
                ->shouldReceive('getResources')->once()->andReturn([])
                ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Set SprinkleManager Mock
        $manager = Mockery::mock(SprinkleManager::class)
                ->shouldReceive('getSprinkles')->once()->andReturn([Foo::class]) // Foo not exist on purpose
                ->getMock();
        $this->ci->set(SprinkleManager::class, $manager);

        // Assert Service is returned.
        $view = $this->ci->get(Twig::class);
        $this->assertInstanceOf(Twig::class, $view);

        // Assert
        $this->expectException(\Twig\Error\SyntaxError::class);
        $view->fetchFromString('{% for alert in getAlerts() %}{{alert.message}}{% endfor %}');
    }
}

class SprinkleStub extends TestSprinkle implements TwigExtensionRecipe
{
    public static function getTwigExtensions(): array
    {
        return [
            TwigAlertsExtension::class,
        ];
    }
}

class SprinkleNotTwigExtensionStub extends TestSprinkle // Don't implement on purpose
{
    public static function getTwigExtensions(): array
    {
        return [
            TwigAlertsExtension::class,
        ];
    }
}
