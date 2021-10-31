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
use Psr\Container\ContainerInterface;
use UserFrosting\Cache\MemcachedStore;
use UserFrosting\Cache\RedisStore;
use UserFrosting\Cache\TaggableFileStore;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Exceptions\BadConfigException;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Setup cache service.
 *
 * @throws BadConfigException If cache handler is not supported
 */
class CacheService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            Cache::class => function (ContainerInterface $ci, Config $config) {
                switch ($config->get('cache.driver')) {
                    case 'file':
                        return $ci->get(TaggableFileStore::class)->instance();
                    break;
                    case 'memcached':
                        return $ci->get(MemcachedStore::class)->instance();
                    break;
                    case 'redis':
                        return $ci->get(RedisStore::class)->instance();
                    break;
                    default:
                        throw new BadConfigException("Bad cache store type '{$config->get('cache.driver')}' specified in configuration file.");
                    break;
                }
            },

            /**
             * Inject path from locator into TaggableFileStore.
             */
            TaggableFileStore::class => function (ResourceLocatorInterface $locator) {
                $path = $locator->findResource('cache://', true, true);

                return new TaggableFileStore($path);
            },

            /**
             * Inject memcached config array into MemcachedStore. Also add common "prefix" key into config.
             */
            MemcachedStore::class => function (Config $config) {
                $config = array_merge($config->get('cache.memcached'), ['prefix' => $config->get('cache.prefix')]);

                return new MemcachedStore($config);
            },

            /**
             * Inject Redis config array into RedisStore. Also add common "prefix" key into config.
             */
            RedisStore::class => function (Config $config) {
                $config = array_merge($config->get('cache.redis'), ['prefix' => $config->get('cache.prefix')]);

                return new RedisStore($config);
            },
        ];
    }
}
