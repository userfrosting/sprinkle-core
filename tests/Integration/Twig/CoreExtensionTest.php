<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Twig;

use DI\Container;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;
use UserFrosting\Sprinkle\Core\Twig\CoreExtension;
use UserFrosting\Testing\ContainerStub;

use UserFrosting\Assets\Assets;
use UserFrosting\Assets\AssetsTemplatePlugin;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\Util\Util;
use UserFrosting\Support\Repository\Repository as Config;


/**
 * CoreExtensionTest class.
 * Tests Core twig extensions
 */
// TODO : CoreExtension should be separated in multiple Extension and registered in CoreRecipe.
// TODO : Could be revised similar to TwigRuntimeExtension. Then each sub function / filter / etc.  can be tested individually. 
//        We would only need to test here that the Extensions are really loaded (by mocking the Extension probably).
class CoreExtensionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;
    protected CoreExtension $extension;
    protected Twig $view;

    public function setUp(): void
    {
        parent::setUp();

        // Create stub container
        $this->ci = ContainerStub::create();

        // Set dependencies services
        $this->view = Twig::create(__DIR__);
    }

    // TODO : 
    /*public function testGetAlerts(): void
    {
        $alerts = Mockery::mock(AlertStream::class)->shouldReceive('getAndClearMessages')->once()->andReturn([
            ['message' => 'foo'],
            ['message' => 'bar'],
        ])->getMock();
        $this->ci->set(AlertStream::class, $alerts);
        $this->ci->set(Assets::class, Mockery::mock(Assets::class));
        $config = Mockery::mock(Config::class)->shouldReceive('get')->with('site')->once()->andReturn([])->getMock();
        $this->ci->set(Config::class, $config);
        $this->ci->set(SiteLocale::class, Mockery::mock(SiteLocale::class));
        // TODO : Mock the rest... but we just want to test alerts! This needs a rewrite...
        $this->ci->set(Translator::class, Mockery::mock(Translator::class));

        // TODO : This is probably fine. But it need to be separated per Extension 
        $this->view->addExtension($this->ci->get(CoreExtension::class));

        $result = $this->view->fetchFromString('{% for alert in getAlerts() %}{{alert.message}}{% endfor %}');
        $this->assertSame('foobar', $result);
    }*/

    /*public function testGetAlertsNoClear(): void
    {
        $this->ci->alerts = Mockery::mock(AlertStream::class)->shouldReceive('messages')->once()->andReturn([
            ['message' => 'foo'],
            ['message' => 'bar'],
        ])->getMock();

        $result = $this->ci->view->fetchFromString('{% for alert in getAlerts(false) %}{{alert.message}}{% endfor %}');
        $this->assertSame('foobar', $result);
    }*/

    /**
     * @see https://github.com/userfrosting/UserFrosting/issues/1090
     */
    /*public function testTranslateFunction(): void
    {
        $result = $this->ci->view->fetchFromString('{{ translate("USER", 2) }}');
        $this->assertSame('Users', $result);
    }*/

    /*public function testPhoneFilter(): void
    {
        $result = $this->ci->view->fetchFromString('{{ data|phone }}', ['data' => '5551234567']);
        $this->assertSame('(555) 123-4567', $result);
    }*/

    /*public function testUnescapeFilter(): void
    {
        $string = "I'll \"walk\" the <b>dog</b> now";
        $this->assertNotSame($string, $this->ci->view->fetchFromString('{{ foo }}', ['foo' => htmlentities($string)]));
        $this->assertNotSame($string, $this->ci->view->fetchFromString('{{ foo|unescape }}', ['foo' => htmlentities($string)]));
        $this->assertNotSame($string, $this->ci->view->fetchFromString('{{ foo|raw }}', ['foo' => htmlentities($string)]));
        $this->assertSame($string, $this->ci->view->fetchFromString('{{ foo|unescape|raw }}', ['foo' => htmlentities($string)]));
    }*/

    /*public function testCurrentLocaleGlobal(): void
    {
        $this->ci->locale = Mockery::mock(SiteLocale::class)->shouldReceive('getLocaleIdentifier')->once()->andReturn('zz-ZZ')->getMock();

        $this->assertSame('zz-ZZ', $this->ci->view->fetchFromString('{{ currentLocale }}'));
    }*/
}
