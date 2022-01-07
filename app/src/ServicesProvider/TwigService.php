<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\DebugExtension;
use Twig\Extension\ExtensionInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\RecipeExtensionLoader;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/*
 * Set up Twig as the view, adding template paths for all sprinkles and the Slim Twig extension.
 *
 * Also adds the UserFrosting core Twig extension, which provides additional functions, filters, global variables, etc.
 */
class TwigService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            Twig::class => function (
                ResourceLocatorInterface $locator,
                Config $config,
                RecipeExtensionLoader $extensionLoader,
            ) {
                $templatePaths = $locator->getResources('templates://');
                $templatePathsStrings = array_map('strval', $templatePaths);
                $twig = Twig::create($templatePathsStrings);
                $loader = $twig->getLoader();

                // Add Sprinkles' templates namespaces
                foreach (array_reverse($templatePaths) as $templateResource) {
                    $loader->addPath($templateResource->getAbsolutePath(), $templateResource->getLocation()->getSlug());
                }

                $twigEnv = $twig->getEnvironment();

                if ($config->get('cache.twig')) {
                    $resource = $locator->getResource('cache://twig', true);
                    $path = $resource?->getAbsolutePath() ?? false;
                    $twigEnv->setCache($path);
                }

                if ($config->get('debug.twig')) {
                    $twigEnv->enableDebug();
                    $twig->addExtension(new DebugExtension());
                }

                // Register Twig extensions
                $this->registerTwigExtensions($twig, $extensionLoader);

                return $twig;
            },

            TwigMiddleware::class => function (App $app, Twig $twig) {
                return TwigMiddleware::create($app, $twig);
            },
        ];
    }

    /**
     * Register all Twig Extensions defined in Sprinkles TwigExtensionRecipe.
     */
    protected function registerTwigExtensions(Twig $twig, RecipeExtensionLoader $extensionLoader): void
    {
        $extensions = $extensionLoader->getInstances(
            method: 'getTwigExtensions',
            recipeInterface: TwigExtensionRecipe::class,
            extensionInterface: ExtensionInterface::class,
        );

        foreach ($extensions as $extension) {
            $twig->addExtension($extension);
        }
    }
}
