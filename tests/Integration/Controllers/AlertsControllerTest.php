<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Controller;

use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Tests AlertsController class.
 */
class AlertsControllerTest extends TestCase
{
    public function testJsonAlerts(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/alerts');
        $response = $this->handleRequest($request);

        // Assert 200 response
        $this->assertSame($response->getStatusCode(), 200);

        // Assert response body
        $this->assertResponseJson([], $response);
    }
}
