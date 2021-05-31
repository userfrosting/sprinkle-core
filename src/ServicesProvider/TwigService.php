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
use Slim\Views\TwigExtension;
use Twig\Extension\DebugExtension;
use UserFrosting\Sprinkle\Core\Twig\CoreExtension;
use UserFrosting\Support\Repository\Repository as Config;

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
            // TODO : Could be useful to have interface
            // TODO : Reimplements extenions
            Twig::class => function (ResourceLocatorInterface $locator, Config $config) {
                $templatePaths = $locator->getResources('templates://');
                $view = new Twig(array_map('strval', $templatePaths));
                $loader = $view->getLoader();

                // Add Sprinkles' templates namespaces
                foreach (array_reverse($templatePaths) as $templateResource) {
                    $loader->addPath($templateResource->getAbsolutePath(), $templateResource->getLocation()->getName());
                }

                $twig = $view->getEnvironment();

                if ($config['cache.twig']) {
                    $twig->setCache($locator->findResource('cache://twig', true, true));
                }

                if ($config['debug.twig']) {
                    $twig->enableDebug();
                    $view->addExtension(new DebugExtension());
                }

                // Register the Slim extension with Twig
                /*$slimExtension = new TwigExtension(
                    $c->router,
                    $c->request->getUri()
                );
                $view->addExtension($slimExtension);*/

                // Register the core UF extension with Twig
                // $coreExtension = new CoreExtension($c);
                // $view->addExtension($coreExtension);

                return $view;
            },
        ];
    }
}
