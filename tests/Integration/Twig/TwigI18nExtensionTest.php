<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Twig;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\Twig\Extensions\TwigI18nExtension;

/**
 * Tests for TwigI18nExtension class.
 * Tests Translate twig extensions
 */
class TwigI18nExtensionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testTranslateIntegration(): void
    {
        $translator = Mockery::mock(Translator::class)
                    ->shouldReceive('translate')
                    ->with('USER', 2)
                    ->once()
                    ->andReturn('foobar')
                    ->getMock();

        // Create and add to extensions.
        $extensions = new TwigI18nExtension($translator);

        // Create dumb Twig and test adding extension
        $view = Twig::create('');
        $view->addExtension($extensions);

        $result = $view->fetchFromString('{{ translate("USER", 2) }}');
        $this->assertSame('foobar', $result);
    }
}
