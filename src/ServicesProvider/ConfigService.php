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
use UserFrosting\Config\ConfigPathBuilder;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/*
 * Site config service.
 *
 * Will attempt to automatically determine which config file(s) to use based on the value of the UF_MODE environment variable.
 */
class ConfigService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO : Use interface & Implement request ?
            // TODO : Inject Dotenv & others classes
            Config::class => function (ResourceLocatorInterface $locator) {
                // Grab any relevant dotenv variables from the .env file
                // located at the locator base path
                try {
                    $dotenv = Dotenv::createImmutable($locator->getBasePath());
                    $dotenv->load();
                } catch (InvalidPathException $e) {
                    // Skip loading the environment config file if it doesn't exist.
                }

                // Get configuration mode from environment
                // TODO : Change to env. It doesn't looks likes it work with dotenv load above.
                // $mode = env('UF_MODE', '');
                $mode = getenv('UF_MODE') ?: '';

                // Construct and load config repository
                $builder = new ConfigPathBuilder($locator, 'config://');
                $loader = new ArrayFileLoader($builder->buildPaths($mode));
                $config = new Config($loader->load());

                // Construct base url from components, if not explicitly specified
                // TODO : Request not in CI yet
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
        ];
    }
}
