<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Controller;

use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Tests CoreController class.
 */
class AssetsControllerTest extends CoreTestCase
{
    protected string $assetsPath = '';

    public function setUp(): void
    {
        parent::setUp();

        // Get config service
        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $this->assetsPath = $config['assets.raw.path'];

        // Set test assets location
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);
        $locator->removeStream('assets')->registerStream('assets', '', __DIR__ . '/data', true);
    }

    /**
     * Test with non-existent asset.
     */
    public function testGetAssetWithInaccessibleAsset(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/' . $this->assetsPath . '/forbidden.txt');
        $response = $this->handleRequest($request);

        // Assert 404 response
        $this->assertSame(404, $response->getStatusCode());

        // Assert empty response body
        $this->assertResponse('', $response);
    }

    /**
     * Test with existent asset.
     */
    public function testGetAsset(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/' . $this->assetsPath . '/allowed.txt');
        $response = $this->handleRequest($request);

        // Assert 200 response
        $this->assertSame(200, $response->getStatusCode());

        // Assert response body matches file
        $this->assertResponse(file_get_contents(__DIR__ . '/data/allowed.txt'), $response);

        // Assert correct MIME
        $this->assertSame(['text/plain'], $response->getHeader('Content-Type'));
    }
}
