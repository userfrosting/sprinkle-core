<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2021 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Psr\Container\ContainerInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\Assets\Assets;
use UserFrosting\Config\ConfigPathBuilder;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;
use UserFrosting\Support\Repository\Repository;

class Services implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
             /*
              * Site config service (separate from Slim settings).
              *
              * Will attempt to automatically determine which config file(s) to use based on the value of the UF_MODE environment variable.
              *
              * @return \UserFrosting\Support\Repository\Repository
              */
             'config' => function (ContainerInterface $c) {
                // Grab any relevant dotenv variables from the .env file
                try {
                   $dotenv = Dotenv::createImmutable(\UserFrosting\APP_DIR);
                   $dotenv->load();
                } catch (InvalidPathException $e) {
                   // Skip loading the environment config file if it doesn't exist.
                }
          
                // Get configuration mode from environment
                // TODO : Change to env. It doesn't looks likes it work with dotenv load above.
                // $mode = env('UF_MODE', '');
                $mode = getenv('UF_MODE') ?: '';
          
                // Construct and load config repository
                $builder = new ConfigPathBuilder($c->get('locator'), 'config://');
                $loader = new ArrayFileLoader($builder->buildPaths($mode));
                $config = new Repository($loader->load());
          
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
