<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Router;

/**
 * Integration tests for `router` service.
 * Check to see if service returns what it's supposed to return
 */
// TODO : Require new router service. Disabled for now.
class RouterServiceTest extends TestCase
{
    /*public function testService()
    {
        $this->assertInstanceOf(Router::class, $this->ci->router);
    }*/

    /**
     * @depends testService
     * Test router integration in Tests
     */
    /*public function testBasicTest()
    {
        /** @var \UserFrosting\Sprinkle\Core\Router $router * /
        $router = $this->ci->router;

        // Get all routes. We should have more than 0 in a default install
        $routes = $router->getRoutes();
        $this->assertNotCount(0, $routes);

        // Try to get a path
        $path = $router->pathFor('index');
        $this->assertEquals('/', $path);
    }*/
}
