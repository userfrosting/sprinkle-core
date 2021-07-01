<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use DI\Container;
use ReflectionClass;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\DebugExtension;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\SprinkleManager;
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
                SprinkleManager $sprinkleManager,
                Container $ci,
            ) {
                $templatePaths = $locator->getResources('templates://');
                $twig = Twig::create(array_map('strval', $templatePaths));
                $loader = $twig->getLoader();

                // Add Sprinkles' templates namespaces
                foreach (array_reverse($templatePaths) as $templateResource) {
                    $loader->addPath($templateResource->getAbsolutePath(), $templateResource->getLocation()->getName());
                }

                $twigEnv = $twig->getEnvironment();

                if ($config->get('cache.twig')) {
                    $twigEnv->setCache($locator->findResource('cache://twig', true, true));
                }

                if ($config->get('debug.twig')) {
                    $twigEnv->enableDebug();
                    $twig->addExtension(new DebugExtension());
                }

                // Register the core UF extension with Twig
                $this->registerTwigExtensions($twig, $sprinkleManager, $ci);

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
    protected function registerTwigExtensions(Twig $twig, SprinkleManager $sprinkleManager, Container $ci): void
    {
        foreach ($sprinkleManager->getSprinkles() as $sprinkle) {
            if ($this->validateClassIsTwigExtensionRecipe($sprinkle)) {
                foreach ($sprinkle::getTwigExtensions() as $extension) {
                    $instance = $ci->get($extension);
                    $twig->addExtension($instance);
                }
            }
        }
    }

    /**
     * Validate the class implements SprinkleRecipe.
     *
     * @param string $class
     *
     * @return bool True/False if class implements SprinkleRecipe
     */
    protected function validateClassIsTwigExtensionRecipe(string $class): bool
    {
        if (!class_exists($class)) {
            return false;
        }

        $class = new ReflectionClass($class);
        if ($class->implementsInterface(TwigExtensionRecipe::class)) {
            return true;
        }

        return false;
    }
}
