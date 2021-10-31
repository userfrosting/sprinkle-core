<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Psr\Container\ContainerInterface;
use UserFrosting\Config\ConfigPathBuilder;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Site config service.
 *
 * Will attempt to automatically determine which config file(s) to use based on the value of the UF_MODE environment variable.
 */
// TODO : Config required rework in the framework. It required too much info to setup, not enough injection (bad code smell).
// Also, DotEnv usage to get the `.env` must be properly tested. The file location must be set somewhere too.
class ConfigService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // Set env mode in CI
            'UF_MODE' => function (ResourceLocatorInterface $locator) {
                // Grab any relevant dotenv variables from the .env file
                // located at the locator base path
                try {
                    $dotenv = Dotenv::createImmutable($locator->getBasePath());
                    $dotenv->load();
                } catch (InvalidPathException $e) {
                    // Skip loading the environment config file if it doesn't exist.
                }

                return env('UF_MODE') ?: '';
            },

            Config::class => function (ArrayFileLoader $loader) {

                // Load config repository
                $config = new Config($loader->load());

                // Construct base url from components, if not explicitly specified
                // TODO : Request not in CI. Move to Middleware
                /*if (!isset($config['site.uri.public'])) {
                      $uri = $c->get('request')->getUri();

                      // Slim\Http\Uri likes to add trailing slashes when the path is empty, so this fixes that.
                      $config['site.uri.public'] = trim($uri->getBaseUrl(), '/');
                   }*/

                // Hacky fix to prevent sessions from being hit too much: ignore CSRF middleware for requests for raw assets ;-)
                // See https://github.com/laravel/framework/issues/8172#issuecomment-99112012 for more information on why it's bad to hit Laravel sessions multiple times in rapid succession.
                $csrfBlacklist = $config['csrf.blacklist'];
                $csrfBlacklist['^/' . $config['assets.raw.path']] = [
                    'GET',
                ];

                $config->set('csrf.blacklist', $csrfBlacklist);

                return $config;
            },

            ArrayFileLoader::class => function (ConfigPathBuilder $builder, ContainerInterface $ci) {
                $mode = $ci->get('UF_MODE');

                return new ArrayFileLoader($builder->buildPaths($mode));
            },

            ConfigPathBuilder::class => function (ResourceLocatorInterface $locator) {
                return new ConfigPathBuilder($locator, 'config://');
            },
        ];
    }
}
