<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use UserFrosting\Assets\AssetLoader;
use UserFrosting\Assets\Assets;
use UserFrosting\Assets\AssetBundles\GulpBundleAssetsCompiledBundles as CompiledAssetBundles;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Util\RawAssetBundles;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class AssetService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            AssetLoader::class => function (Assets $assets) {
                return new AssetLoader($assets);
            },

            Assets::class => function (Config $config, ResourceLocatorInterface $locator) {

                // Load asset schema
                if ($config['assets.use_raw']) {

                    // Register sprinkle assets stream, plus vendor assets in shared streams
                    $locator->registerStream('assets', 'vendor', \UserFrosting\NPM_ASSET_DIR, true);
                    $locator->registerStream('assets', 'vendor', \UserFrosting\BROWSERIFIED_ASSET_DIR, true);
                    $locator->registerStream('assets', 'vendor', \UserFrosting\BOWER_ASSET_DIR, true);
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
        ];
    }
}
