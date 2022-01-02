<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\I18n;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Psr\Http\Message\ResponseInterface as Response;
use UserFrosting\I18n\Locale;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Tests SiteLocale.
 *
 * N.B.: This requires the full App stack, since locale files will be loaded.
 */
class SiteLocaleTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $testLocale = [
        'fr_FR' => 'french', // Legacy setting
        'en_US' => true,
        'es_ES' => false,
        'it_IT' => null, // Legacy setting
    ];

    protected Config $config;
    protected SiteLocale $locale;

    // Apply fake config
    public function setUp(): void
    {
        parent::setUp();

        // Set alias
        $this->config = $this->ci->get(Config::class);
        $this->locale = $this->ci->get(SiteLocale::class);

        // Set test config
        $this->config->set('site.locales.available', $this->testLocale);
    }

    public function testService(): void
    {
        $this->assertInstanceOf(SiteLocale::class, $this->locale);
    }

    public function testFakeConfig(): void
    {
        $this->assertSame($this->testLocale, $this->config->get('site.locales.available'));
    }

    /**
     * @depends testService
     * @depends testFakeConfig
     */
    public function testGetAvailableIdentifiers(): void
    {
        $this->assertSame([
            'fr_FR',
            'en_US',
        ], $this->locale->getAvailableIdentifiers());
    }

    /**
     * @depends testService
     * @depends testFakeConfig
     */
    public function testgetAvailable(): void
    {
        $locales = $this->locale->getAvailable();

        $this->assertIsArray($locales);
        $this->assertCount(2, $locales);
        $this->assertInstanceOf(Locale::class, $locales[0]);
    }

    /**
     * @depends testgetAvailable
     */
    public function testgetAvailableOptions(): void
    {
        // Implement fake locale file location & locator
        $locator = new ResourceLocator(__DIR__);
        $stream = new ResourceStream('locale', 'data', true);
        $locator->addStream($stream);

        // Set expectations. Note the sort applied here
        $expected = [
            'en_US' => 'English',
            'fr_FR' => 'Tomato', // Just to be sure the fake locale are loaded ;)
        ];

        $options = $this->locale->getAvailableOptions();

        $this->assertIsArray($options);
        $this->assertSame($expected, $options);
    }

    /**
     * @depends testGetAvailableIdentifiers
     */
    public function testIsAvailable(): void
    {
        $this->assertFalse($this->locale->isAvailable('ZZ_zz'));
        $this->assertFalse($this->locale->isAvailable('es_ES'));
        $this->assertTrue($this->locale->isAvailable('en_US'));
    }

    /**
     * Will return the default locale (fr_FR)
     */
    public function testGetLocaleIdentifier(): void
    {
        $this->config->set('site.locales.default', 'fr_FR');
        $this->assertSame('fr_FR', $this->locale->getLocaleIdentifier());
    }

    /**
     * Will return en_US
     */
    public function testGetLocaleIdentifierWithDefaultIdentifier(): void
    {
        $this->config->set('site.locales.default', '');
        $this->assertSame('en_US', $this->locale->getLocaleIdentifier());
    }

    /**
     * Will return en_US
     */
    public function testGetLocaleIdentifierWithNonStringIdentifier(): void
    {
        $this->config->set('site.locales.default', ['foo', 'bar']);
        $this->assertSame('en_US', $this->locale->getLocaleIdentifier());
    }

    /**
     * Test old method of defining the default locale
     */
    public function testGetLocaleIdentifierWithCommaSeparatedString(): void
    {
        $this->config->set('site.locales.default', 'fr_FR, en_US');
        $this->assertSame('fr_FR, en_US', $this->locale->getLocaleIdentifier());
    }

    /**
     * Test old method of defining the default locale
     */
    public function testGetLocaleIdentifierWithCommaSeparatedStringReverseOrder(): void
    {
        $this->config->set('site.locales.default', 'en_US,fr_FR');
        $this->assertSame('en_US,fr_FR', $this->locale->getLocaleIdentifier());
    }

    public function testGetLocaleIdentifierWithBrowserAndNoHeader(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(false);

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Define browser locale
        $this->locale->defineBrowserLocale($request);

        // Get locale
        $locale = $this->locale->getLocaleIdentifier();

        // Assertions
        $this->assertSame('fr_FR', $locale);
    }

    public function testGetLocaleIdentifierWithBrowserAndComplexLocale(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('en-US, en;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Define browser locale
        $this->locale->defineBrowserLocale($request);

        // Get locale
        $locale = $this->locale->getLocaleIdentifier();

        // Assertions
        $this->assertSame('en_US', $locale);
        $this->assertTrue($this->locale->isAvailable($locale));
    }

    public function testGetLocaleIdentifierWithBrowserAndComplexLocaleInLowerCase(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('en-us, en;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Define browser locale
        $this->locale->defineBrowserLocale($request);

        // Get locale
        $locale = $this->locale->getLocaleIdentifier();

        // Assertions
        $this->assertSame('en_US', $locale);
        $this->assertTrue($this->locale->isAvailable($locale));
    }

    public function testGetLocaleIdentifierWithBrowserAndMultipleLocale(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('es-ES, fr-FR;q=0.7, fr-CA;q=0.9, en-US;q=0.8, *;q=0.5');

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Define browser locale
        $this->locale->defineBrowserLocale($request);

        // Assertions
        $this->assertSame('en_US', $this->locale->getLocaleIdentifier());
    }

    public function testGetLocaleIdentifierWithBrowserAndLocaleInSecondPlace(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('zz-ZZ, en-US;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Define browser locale
        $this->locale->defineBrowserLocale($request);

        // Assertions
        $this->assertSame('en_US', $this->locale->getLocaleIdentifier());
    }

    public function testGetLocaleIdentifierWithBrowserAndInvalidLocale(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('fo,oba;;;r,');

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Define browser locale
        $this->locale->defineBrowserLocale($request);

        // Assertions
        $this->assertSame('fr_FR', $this->locale->getLocaleIdentifier());
    }

    public function testGetLocaleIdentifierWithBrowserAndNonExistingLocale(): void
    {
        // Define mock
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('fr-ca');

        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Define browser locale
        $this->locale->defineBrowserLocale($request);

        // Assertions
        $this->assertSame('fr_FR', $this->locale->getLocaleIdentifier());
    }

    public function testMiddlewareWithSimulatedBrowserLocaleControl(): void
    {
        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Add test route
        $this->app->get('/testMiddlewareWithSimulatedBrowserLocale', ControllerStub::class);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/testMiddlewareWithSimulatedBrowserLocale');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertSame(200, $response->getStatusCode());
        $this->assertResponse('fr_FR', $response);
    }

    /**
     * @depends testMiddlewareWithSimulatedBrowserLocaleControl
     */
    public function testMiddlewareWithSimulatedBrowserLocale(): void
    {
        // Define default locale
        $this->config['site.locales.default'] = 'fr_FR';

        // Add test route
        $this->app->get('/testMiddlewareWithSimulatedBrowserLocale', ControllerStub::class);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/testMiddlewareWithSimulatedBrowserLocale');
        $request = $request->withHeader('Accept-Language', 'en-US');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertSame(200, $response->getStatusCode());
        $this->assertResponse('en_US', $response);
    }
}

class ControllerStub
{
    public function __invoke(Response $response, SiteLocale $siteLocale): Response
    {
        $response->getBody()->write($siteLocale->getLocaleIdentifier());

        return $response;
    }
}
