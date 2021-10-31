<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use UserFrosting\Alert\AlertStream;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test service implementation works.
 * This test is agnostic of the actual config. It only test all the parts works together in a default env.
 */
class AlertStreamServiceTest extends TestCase
{
    public function testService(): void
    {
        $this->assertInstanceOf(AlertStream::class, $this->ci->get(AlertStream::class));
    }
}
