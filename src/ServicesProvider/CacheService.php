<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Illuminate\Cache\Repository as Cache;
use UserFrosting\Cache\MemcachedStore;
use UserFrosting\Cache\RedisStore;
use UserFrosting\Cache\TaggableFileStore;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/*
 * Cache service.
 *
 * @throws \Exception                   If cache handler is not supported
 * @return \Illuminate\Cache\Repository
 */
class CacheService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            Cache::class => function (Config $config, ResourceLocatorInterface $locator) {
                if ($config['cache.driver'] == 'file') {
                    $path = $locator->findResource('cache://', true, true);
                    $cacheStore = new TaggableFileStore($path);
                } elseif ($config['cache.driver'] == 'memcached') {
                    // We need to inject the prefix in the memcached config
                    $config = array_merge($config['cache.memcached'], ['prefix' => $config['cache.prefix']]);
                    $cacheStore = new MemcachedStore($config);
                } elseif ($config['cache.driver'] == 'redis') {
                    // We need to inject the prefix in the redis config
                    $config = array_merge($config['cache.redis'], ['prefix' => $config['cache.prefix']]);
                    $cacheStore = new RedisStore($config);
                } else {
                    throw new \Exception("Bad cache store type '{$config['cache.driver']}' specified in configuration file.");
                }

                return $cacheStore->instance();
            },
        ];
    }
}
