<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Twig;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;
use UserFrosting\Sprinkle\Core\Twig\Extensions\CoreExtension;

use UserFrosting\Support\Repository\Repository as Config;

/**
 * CoreExtensionTest class.
 * Tests Core twig extensions
 */
class CoreExtensionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Twig $view;

    public function setUp(): void
    {
        parent::setUp();

        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('site')->andReturn(['foo' => 'bar'])
            ->getMock();

        // Create and add to extensions.
        $extensions = new CoreExtension($config);

        // Create dumb Twig and test adding extension
        $this->view = Twig::create('');
        $this->view->addExtension($extensions);
    }

    public function testPhoneFilter(): void
    {
        $result = $this->view->fetchFromString('{{ data|phone }}', ['data' => '5551234567']);
        $this->assertSame('(555) 123-4567', $result);
    }

    public function testUnescapeFilter(): void
    {
        $string = "I'll \"walk\" the <b>dog</b> now";
        $this->assertNotSame($string, $this->view->fetchFromString('{{ foo }}', ['foo' => htmlentities($string)]));
        $this->assertNotSame($string, $this->view->fetchFromString('{{ foo|unescape }}', ['foo' => htmlentities($string)]));
        $this->assertNotSame($string, $this->view->fetchFromString('{{ foo|raw }}', ['foo' => htmlentities($string)]));
        $this->assertSame($string, $this->view->fetchFromString('{{ foo|unescape|raw }}', ['foo' => htmlentities($string)]));
    }

    public function testGlobal(): void
    {
        $this->assertSame('bar', $this->view->fetchFromString('{{ site.foo }}'));
    }
}
