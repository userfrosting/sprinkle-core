<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\I18n;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\I18n\Locale;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;

/**
 * Tests SiteLocale.
 * 
 * N.B.: This requires the full App stack, since locale files will be loaded.
 */
class SiteLocaleTest extends CoreTestCase
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
        $locator->registerStream('locale', '', 'data', true);

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
    public function testGetLocaleIndentifier(): void
    {
        $this->config->set('site.locales.default', 'fr_FR');
        $this->assertSame('fr_FR', $this->locale->getLocaleIndentifier());
    }

    /**
     * Will return en_US
     */
    public function testGetLocaleIndentifierWithDefaultIndentifier(): void
    {
        $this->config->set('site.locales.default', '');
        $this->assertSame('en_US', $this->locale->getLocaleIndentifier());
    }

    /**
     * Will return en_US
     */
    public function testGetLocaleIndentifierWithNonStringIndentifier(): void
    {
        $this->config->set('site.locales.default', ['foo', 'bar']);
        $this->assertSame('en_US', $this->locale->getLocaleIndentifier());
    }

    /**
     * Test old method of defining the default locale
     */
    public function testGetLocaleIndentifierWithCommaSeparatedString(): void
    {
        $this->config->set('site.locales.default', 'fr_FR, en_US');
        $this->assertSame('fr_FR, en_US', $this->locale->getLocaleIndentifier());
    }

    /**
     * Test old method of defining the default locale
     */
    public function testGetLocaleIndentifierWithCommaSeparatedStringReverseOrder(): void
    {
        $this->config->set('site.locales.default', 'en_US,fr_FR');
        $this->assertSame('en_US,fr_FR', $this->locale->getLocaleIndentifier());
    }

    /*public function testGetLocaleIndentifierWithBrowserAndComplexLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('en-US, en;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');

        $this->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;

        // Get locale
        $locale = $this->locale->getLocaleIndentifier();

        // Assertions
        $this->assertSame('en_US', $locale);
        $this->assertTrue($this->locale->isAvailable($locale));
    }

    public function testGetLocaleIndentifierWithBrowserAndComplexLocaleInLowerCase(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('en-us, en;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');

        $this->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;

        // Get locale
        $locale = $this->locale->getLocaleIndentifier();

        $this->assertSame('en_US', $locale);
        $this->assertTrue($this->locale->isAvailable($locale));
    }

    public function testGetLocaleIndentifierWithBrowserAndMultipleLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('es-ES, fr-FR;q=0.7, fr-CA;q=0.9, en-US;q=0.8, *;q=0.5');

        $this->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $this->assertSame('en_US', $this->locale->getLocaleIndentifier());
    }

    public function testGetLocaleIndentifierWithBrowserAndLocaleInSecondPlace(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('zz-ZZ, en-US;q=0.9, fr;q=0.8, de;q=0.7, *;q=0.5');

        $this->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $this->assertSame('en_US', $this->locale->getLocaleIndentifier());
    }

    public function testGetLocaleIndentifierWithBrowserAndInvalidLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('fo,oba;;;r,');

        $this->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $this->assertSame('fr_FR', $this->locale->getLocaleIndentifier());
    }

    public function testGetLocaleIndentifierWithBrowserAndNonExistingLocale(): void
    {
        $request = m::mock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')->with('Accept-Language')->once()->andReturn(true);
        $request->shouldReceive('getHeaderLine')->with('Accept-Language')->once()->andReturn('fr-ca');

        $this->config['site.locales.default'] = 'fr_FR';
        $this->ci->request = $request;
        $this->assertSame('fr_FR', $this->locale->getLocaleIndentifier());
    }*/
}
