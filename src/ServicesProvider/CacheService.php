<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
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
            // TODO : Custom exception should be used.
            // TODO : Container could be used to instantiate *Store and limit the number of dependencies required. Plus Locator is only required in 1 of 3 cases...
            //        (but would depend on the whole container... PHP-DI docs should be consulted to find the best way to do this).
            Cache::class => function (Config $config, ResourceLocatorInterface $locator) {
                switch ($config->get('cache.driver')) {
                    case 'file':
                        $path = $locator->findResource('cache://', true, true);
                        $cacheStore = new TaggableFileStore($path);
                    break;
                    case 'memcached':
                        // We need to inject the prefix in the memcached config
                        $config = array_merge($config->get('cache.memcached'), ['prefix' => $config->get('cache.prefix')]);
                        $cacheStore = new MemcachedStore($config);
                    break;
                    case 'redis':
                        // We need to inject the prefix in the redis config
                        $config = array_merge($config->get('cache.redis'), ['prefix' => $config->get('cache.prefix')]);
                        $cacheStore = new RedisStore($config);
                    break;
                    default:
                        throw new \Exception("Bad cache store type '{$config->get('cache.driver')}' specified in configuration file.");
                    break;
                }

                return $cacheStore->instance();
            },
        ];
    }
}
