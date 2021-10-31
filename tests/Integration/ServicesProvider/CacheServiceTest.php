<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Illuminate\Cache\Repository as Cache;
use UserFrosting\Cache\MemcachedStore;
use UserFrosting\Cache\RedisStore;
use UserFrosting\Cache\TaggableFileStore;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test service implementation works.
 * This test is agnostic of the actual config. It only test all the parts works together in a default env.
 */
class CacheServiceTest extends TestCase
{
    public function testService(): void
    {
        $this->assertInstanceOf(Cache::class, $this->ci->get(Cache::class));
        $this->assertInstanceOf(TaggableFileStore::class, $this->ci->get(TaggableFileStore::class));
        $this->assertInstanceOf(MemcachedStore::class, $this->ci->get(MemcachedStore::class));
        $this->assertInstanceOf(RedisStore::class, $this->ci->get(RedisStore::class));
    }
}
