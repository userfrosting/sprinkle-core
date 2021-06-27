<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use UserFrosting\I18n\Translator;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Tests AccountController
 */
class I18nServiceTest extends TestCase
{
    /**
     * @var bool[] Available locale for test
     */
    protected $testLocale = [
        'fr_FR' => 'french',  // Legacy setting
        'en_US' => true,
        'es_ES' => false,
    ];

    protected Config $config;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();

        // Set alias
        $this->config = $this->ci->get(Config::class);

        // Set test config
        $this->config->set('site.locales.available', $this->testLocale);
    }

    /**
     * Will return the default locale (fr_FR)
     */
    public function testActualService(): void
    {
        $this->config->set('site.locales.default', 'fr_FR');
        $translator = $this->ci->get(Translator::class);
        $this->assertInstanceOf(Translator::class, $translator);
        $this->assertSame('fr_FR', $translator->getLocale()->getIdentifier());
    }

    /**
     * Will return en_US
     */
    public function testActualServiceWithDefaultIndentifier(): void
    {
        $this->config->set('site.locales.default', '');
        $translator = $this->ci->get(Translator::class);
        $this->assertInstanceOf(Translator::class, $translator);
        $this->assertSame('en_US', $translator->getLocale()->getIdentifier());
    }

    /**
     * Will return en_US
     */
    public function testActualServiceWithNonStringIndentifier(): void
    {
        $this->config->set('site.locales.default', ['foo', 'bar']);
        $translator = $this->ci->get(Translator::class);
        $this->assertInstanceOf(Translator::class, $translator);
        $this->assertSame('en_US', $translator->getLocale()->getIdentifier());
    }
}
