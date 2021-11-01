<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test service implementation works.
 * This test is agnostic of the actual config. It only test all the parts works together in a default env.
 */
class DatabaseServiceTest extends TestCase
{
    public function testService(): void
    {
        $this->assertInstanceOf(Capsule::class, $this->ci->get(Capsule::class));
        $this->assertInstanceOf(Connection::class, $this->ci->get(Connection::class));
        $this->assertInstanceOf(Builder::class, $this->ci->get(Builder::class));
    }
}
