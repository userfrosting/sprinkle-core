<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Slim\Interfaces\RouteParserInterface;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test service implementation works
 */
class RoutingServiceTest extends TestCase
{
    public function testService(): void
    {
        $this->assertInstanceOf(RouteParserInterface::class, $this->ci->get(RouteParserInterface::class));
    }
}
