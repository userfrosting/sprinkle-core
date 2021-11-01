<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\FileSessionHandler;
use SessionHandlerInterface;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test service implementation works.
 * This test is agnostic of the actual config. It only test all the parts works together in a default env.
 */
class SessionServiceTest extends TestCase
{
    public function testService(): void
    {
        $this->assertInstanceOf(Session::class, $this->ci->get(Session::class));
        $this->assertInstanceOf(SessionHandlerInterface::class, $this->ci->get(SessionHandlerInterface::class));
        $this->assertInstanceOf(FileSessionHandler::class, $this->ci->get(FileSessionHandler::class));
        $this->assertInstanceOf(DatabaseSessionHandler::class, $this->ci->get(DatabaseSessionHandler::class));
    }
}
