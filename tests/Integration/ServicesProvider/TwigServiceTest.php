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
use Slim\App;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\ExtensionInterface;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Sprinkle\Core\ServicesProvider\TwigService;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\Core\Twig\Extensions\TwigAlertsExtension;
use UserFrosting\Sprinkle\RecipeExtensionLoader;
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

    public function testMiddleware(): void
    {
        // Set App Mock
        $RouteParserInterface = Mockery::mock(RouteParserInterface::class);
        $RouteCollectorInterface = Mockery::mock(RouteCollectorInterface::class)
            ->shouldReceive('getRouteParser')->andReturn($RouteParserInterface)
            ->getMock();
        $app = Mockery::mock(App::class)
            ->shouldReceive('getRouteCollector')->andReturn($RouteCollectorInterface)
            ->shouldReceive('getBasePath')->andReturn('')
            ->getMock();
        $this->ci->set(App::class, $app);

        $twig = Mockery::mock(Twig::class);
        $this->ci->set(Twig::class, $twig);

        $this->assertInstanceOf(TwigMiddleware::class, $this->ci->get(TwigMiddleware::class));
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
        $location = Mockery::mock(ResourceLocationInterface::class)
                ->shouldReceive('getName')->andReturn('foobar')
                ->getMock();
        $resource = Mockery::mock(ResourceInterface::class)
                ->shouldReceive('getAbsolutePath')->andReturn(__DIR__)
                ->shouldReceive('__toString')->andReturn(__DIR__)
                ->shouldReceive('getLocation')->andReturn($location)
                ->getMock();
        $locator = Mockery::mock(ResourceLocatorInterface::class)
                ->shouldReceive('getResources')->with('templates://')->once()->andReturn([$resource])
                ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Define mock AlertStream
        $results = [
            ['message' => 'foo'],
            ['message' => 'bar'],
        ];
        $alertStream = Mockery::mock(AlertStream::class)
                ->shouldReceive('getAndClearMessages')
                ->once()
                ->andReturn($results)
                ->getMock();

        // Init TwigAlertsExtension
        $extension = new TwigAlertsExtension($alertStream);

        // Set SprinkleManager Mock
        $manager = Mockery::mock(RecipeExtensionLoader::class)
                ->shouldReceive('getInstances')
                ->with('getTwigExtensions', TwigExtensionRecipe::class, ExtensionInterface::class)
                ->once()
                ->andReturn([$extension])
                ->getMock();
        $this->ci->set(RecipeExtensionLoader::class, $manager);

        // Assert Service is returned.
        $view = $this->ci->get(Twig::class);
        $this->assertInstanceOf(Twig::class, $view);

        // Assert
        $result = $view->fetchFromString('{% for alert in getAlerts() %}{{alert.message}}{% endfor %}');
        $this->assertSame('foobar', $result);
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
        $manager = Mockery::mock(RecipeExtensionLoader::class)
                ->shouldReceive('getInstances')
                ->with('getTwigExtensions', TwigExtensionRecipe::class, ExtensionInterface::class)
                ->once()
                ->andReturn([])
                ->getMock();
        $this->ci->set(RecipeExtensionLoader::class, $manager);

        $this->assertInstanceOf(Twig::class, $this->ci->get(Twig::class));
    }
}
