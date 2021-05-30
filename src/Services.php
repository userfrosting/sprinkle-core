<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Psr\Container\ContainerInterface;
use UserFrosting\Assets\AssetLoader;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\Assets\Assets;
use UserFrosting\Config\ConfigPathBuilder;
use UserFrosting\Sprinkle\Core\Util\RawAssetBundles;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\Assets\AssetBundles\GulpBundleAssetsCompiledBundles as CompiledAssetBundles;

class Services implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            ResourceLocatorInterface::class => function (SprinkleManager $sprinkleManager) {
                
                // Create instance based on main sprinkle path
                $mainSprinkle = $sprinkleManager->getMainSprinkle();
                $locator = new ResourceLocator($mainSprinkle::getPath());

                // Register all sprinkles locations
                foreach ($sprinkleManager->getSprinkles() as $sprinkle) {
                    $locator->registerLocation($sprinkle::getName(), $sprinkle::getPath());
                }

                // Register core locator shared streams
                // $locator->registerStream('cache', '', \UserFrosting\APP_DIR . \UserFrosting\DS . \UserFrosting\CACHE_DIR_NAME, true);
                // $locator->registerStream('log', '', \UserFrosting\APP_DIR . \UserFrosting\DS . \UserFrosting\LOG_DIR_NAME, true);
                // $locator->registerStream('session', '', \UserFrosting\APP_DIR . \UserFrosting\DS . \UserFrosting\SESSION_DIR_NAME, true);

                // Register core locator sprinkle streams
                $locator->registerStream('sprinkles', '', '');
                $locator->registerStream('config');
                // $locator->registerStream('extra', '', \UserFrosting\EXTRA_DIR_NAME);
                // $locator->registerStream('factories', '', \UserFrosting\FACTORY_DIR_NAME);
                // $locator->registerStream('locale', '', \UserFrosting\LOCALE_DIR_NAME);
                // $locator->registerStream('routes', '', \UserFrosting\ROUTE_DIR_NAME);
                // $locator->registerStream('schema', '', \UserFrosting\SCHEMA_DIR_NAME);
                // $locator->registerStream('templates', '', \UserFrosting\TEMPLATE_DIR_NAME);

                // Register core sprinkle class streams
                // $locator->registerStream('seeds', '', \UserFrosting\SEEDS_DIR);
                // $locator->registerStream('migrations', '', \UserFrosting\MIGRATIONS_DIR);


                return $locator;
            },

            AssetLoader::class => function (Assets $assets) {
                return new AssetLoader($assets);
            },

            Assets::class => function(Config $config, ResourceLocatorInterface $locator) {

                // Load asset schema
                if ($config['assets.use_raw']) {

                    // Register sprinkle assets stream, plus vendor assets in shared streams
                    // $locator->registerStream('assets', 'vendor', \UserFrosting\NPM_ASSET_DIR, true);
                    // $locator->registerStream('assets', 'vendor', \UserFrosting\BROWSERIFIED_ASSET_DIR, true);
                    // $locator->registerStream('assets', 'vendor', \UserFrosting\BOWER_ASSET_DIR, true);
                    $locator->registerStream('assets', '', \UserFrosting\ASSET_DIR_NAME);

                    $baseUrl = $config['site.uri.public'] . '/' . $config['assets.raw.path'];

                    $assets = new Assets($locator, 'assets', $baseUrl);

                    // Load raw asset bundles for each Sprinkle.

                    // Retrieve locations of raw asset bundle schemas that exist.
                    $bundleSchemas = array_reverse($locator->findResources('sprinkles://' . $config['assets.raw.schema']));

                    // Load asset bundle schemas that exist.
                    if (array_key_exists(0, $bundleSchemas)) {
                        $bundles = new RawAssetBundles(array_shift($bundleSchemas));

                        foreach ($bundleSchemas as $bundleSchema) {
                            $bundles->extend($bundleSchema);
                        }

                        // Add bundles to asset manager.
                        $assets->addAssetBundles($bundles);
                    }
                } else {

                    // Register compiled assets stream in public folder + alias for vendor ones + build stream for CompiledAssetBundles
                    $locator->registerStream('assets', '', \UserFrosting\PUBLIC_DIR_NAME . '/' . \UserFrosting\ASSET_DIR_NAME, true);
                    $locator->registerStream('assets', 'vendor', \UserFrosting\PUBLIC_DIR_NAME . '/' . \UserFrosting\ASSET_DIR_NAME, true);
                    $locator->registerStream('build', '', \UserFrosting\BUILD_DIR_NAME, true);

                    $baseUrl = $config['site.uri.public'] . '/' . $config['assets.compiled.path'];
                    $assets = new Assets($locator, 'assets', $baseUrl);

                    // Load compiled asset bundle.
                    $path = $locator->findResource('build://' . $config['assets.compiled.schema'], true, true);
                    $bundles = new CompiledAssetBundles($path);
                    $assets->addAssetBundles($bundles);
                }

                return $assets;
            },

            /*
             * Site config service (separate from Slim settings).
             *
             * Will attempt to automatically determine which config file(s) to use based on the value of the UF_MODE environment variable.
             *
             * @return \UserFrosting\Support\Repository\Repository
             */
            Config::class => function (ResourceLocatorInterface $locator) {
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

            // Deprecated alias, to remove eventually
            'assets'        => \DI\get(Assets::class),
            'assetLoader'   => \DI\get(AssetLoader::class),
            'config'        => \DI\get(Config::class),
            'locator'       => \DI\get(ResourceLocatorInterface::class),
        ];
    }
}
