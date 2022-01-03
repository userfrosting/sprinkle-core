<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Twig\WebpackEncore;

use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Adds Webpack Encore related function to Twig.
 *
 * Added functions :
 * - encore_entry_js_files(string $entryName)
 * - encore_entry_css_files(string $entryName)
 * - encore_entry_script_tags(string $entryName, array $extraAttributes = [])
 * - encore_entry_link_tags(string $entryName, array $extraAttributes = [])
 *
 * @see https://symfony.com/doc/current/frontend.html
 * @see https://github.com/symfony/webpack-encore-bundle
 * @see https://github.com/symfony/webpack-encore-bundle/blob/509cad50878e838c879743225e0e921b3b64a3f2/src/Twig/EntryFilesTwigExtension.php
 */
final class EntrypointsTwigExtension extends AbstractExtension
{
    /**
     * @param EntrypointLookupInterface $entrypoints
     * @param TagRenderer               $tagRenderer
     */
    public function __construct(
        private EntrypointLookupInterface $entrypoints,
        private TagRenderer $tagRenderer,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_js_files', [$this->entrypoints, 'getJavaScriptFiles']),
            new TwigFunction('encore_entry_css_files', [$this->entrypoints, 'getCssFiles']),
            new TwigFunction('encore_entry_script_tags', [$this->tagRenderer, 'renderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags', [$this->tagRenderer, 'renderWebpackLinkTags'], ['is_safe' => ['html']]),
        ];
    }
}
