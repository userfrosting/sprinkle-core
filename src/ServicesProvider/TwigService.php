<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use Slim\Views\Twig;
use Twig\Extension\DebugExtension;
use UserFrosting\Sprinkle\Core\Twig\CoreExtension;
use UserFrosting\Support\Repository\Repository as Config;
use Slim\App;
use Slim\Views\TwigMiddleware;

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
            // TODO : Reimplements extenions
            Twig::class => function (ResourceLocatorInterface $locator, Config $config) {
                $templatePaths = $locator->getResources('templates://');
                $view = Twig::create(array_map('strval', $templatePaths));
                $loader = $view->getLoader();

                // Add Sprinkles' templates namespaces
                foreach (array_reverse($templatePaths) as $templateResource) {
                    $loader->addPath($templateResource->getAbsolutePath(), $templateResource->getLocation()->getName());
                }

                $twig = $view->getEnvironment();

                if ($config->get('cache.twig')) {
                    $twig->setCache($locator->findResource('cache://twig', true, true));
                }

                if ($config->get('debug.twig')) {
                    $twig->enableDebug();
                    $view->addExtension(new DebugExtension());
                }

                // Register the core UF extension with Twig
                // $coreExtension = new CoreExtension($c);
                // $view->addExtension($coreExtension);

                return $view;
            },

            TwigMiddleware::class => function (App $app, Twig $twig) {
                return TwigMiddleware::create($app, $twig);
            },
        ];
    }
}
