<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Illuminate\Database\Capsule\Manager;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for `debugLogger` service.
 * Check to see if service returns what it's supposed to return
 */
// TODO : Tests disabled. Service require rewriting
class DbServiceTest extends TestCase
{
    /*public function testService()
    {
        $this->assertInstanceOf(Manager::class, $this->ci->db);
    }*/

    /*public function testServiceWithDebug()
    {
        $this->ci->config['debug.queries'] = true;
        $this->assertInstanceOf(Manager::class, $this->ci->db);
    }*/
}
