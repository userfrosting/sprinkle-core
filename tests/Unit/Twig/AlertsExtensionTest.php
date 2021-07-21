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
use UserFrosting\Alert\AlertStream;
use UserFrosting\Sprinkle\Core\Twig\Extensions\AlertsExtension;

/**
 * TwigAlertsExtensionTest class.
 * Tests Alerts twig extensions
 */
class AlertsExtensionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Test TwigAlertsExtension.
     */
    public function testGetAlerts(): void
    {
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

        // Create and assert
        $extensions = new AlertsExtension($alertStream);
        $this->assertSame($results, $extensions->getAlerts());
    }

    /**
     * @depends testGetAlerts
     */
    public function testGetAlertsNoClear(): void
    {
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

        // Create and assert
        $extensions = new AlertsExtension($alertStream);
        $this->assertSame($results, $extensions->getAlerts());
    }

    /**
     * @depends testGetAlerts
     */
    public function testGetAlertsIntegration(): void
    {
        $results = [
            ['message' => 'foo'],
            ['message' => 'bar'],
        ];

        // Define mock AlertStream and register with Container
        $alertStream = Mockery::mock(AlertStream::class)
                    ->shouldReceive('messages')
                    ->once()
                    ->andReturn($results)
                    ->getMock();

        // Create and add to extensions.
        $extensions = new AlertsExtension($alertStream);

        // Create dumb Twig and test adding extension
        $view = Twig::create('');
        $view->addExtension($extensions);

        $result = $view->fetchFromString('{% for alert in getAlerts(false) %}{{alert.message}}{% endfor %}');
        $this->assertSame('foobar', $result);
    }
}
