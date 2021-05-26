<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2021 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Assets;

use Mockery;
use Slim\Http\Environment;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use UserFrosting\Assets\AssetLoader;
use UserFrosting\Assets\Assets;
use UserFrosting\Assets\ServeAsset\SlimServeAsset;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Tests\UserFrostingTestCase;
use UserFrosting\UniformResourceLocator\ResourceLocator;

/**
 * Tests SlimServeAsset class.
 */
class SlimServeAssetTest extends UserFrostingTestCase
{
    protected string $mainSprinkle = Core::class;
    
    /** @var AssetLoader */
    private $assetLoader;

    /**
     * Initializes test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
        
        $basePath = __DIR__.'/../data';
        $baseUrl = 'https://assets.userfrosting.com/assets/';
        $locatorScheme = 'assets';
        $locator = new ResourceLocator($basePath);
        $locator->registerStream($locatorScheme, '', 'assets');
        $locator->registerStream($locatorScheme, 'vendor', 'assets', true);
        $locator->registerLocation('hawks', 'sprinkles/hawks/');
        $locator->registerLocation('owls', 'sprinkles/owls/');

        // Initialize Assets
        $assets = new Assets($locator, $locatorScheme, $baseUrl);

        // Initialize container
        $this->assetLoader = new AssetLoader($assets);
    }

    /**
     * Test with non-existent asset.
     */
    public function testInaccessibleAsset(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/assets-raw/forbidden.txt'); // TODO Change url to config
        $response = $this->handleRequest($request);

        // Assert 404 response
        $this->assertSame($response->getStatusCode(), 404);

        // Assert empty response body
        $this->assertSame($response->getBody()->getContents(), '');
    }

    /**
     * Test with existent asset.
     */
    public function testAssetMatchesExpectations(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/assets-raw/allowed.txt'); // TODO Change url to config
        $response = $this->handleRequest($request);

        // Assert 200 response
        $this->assertSame($response->getStatusCode(), 200);

        // Assert response body matches file
        $this->assertSame((string) $response->getBody(), file_get_contents(__DIR__.'/../data/sprinkles/hawks/assets/allowed.txt'));

        // Assert correct MIME
        $this->assertSame($response->getHeader('Content-Type'), ['text/plain']);
    }

    /**
     * Test with existent asset.
     */
    public function testAssetOfUnknownType(SlimServeAsset $controller): void
    {
        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/assets-raw/mysterious'); // TODO Change url to config
        $response = $this->handleRequest($request);

        // Assert 200 response
        $this->assertSame($response->getStatusCode(), 200);

        // Assert response body matches file
        $this->assertSame((string) $response->getBody(), file_get_contents(__DIR__.'/../data/sprinkles/hawks/assets/mysterious'));

        // Assert correct MIME
        $this->assertSame($response->getHeader('Content-Type'), ['text/plain']);
    }
}
