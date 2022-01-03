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

use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Adds Webpack Encore related function to Twig.
 *
 * Added functions :
 * - asset(string $uri) : Convert asset URI to it's versioned counterpart in `manifest.json`
 *
 * @see https://symfony.com/doc/current/frontend/encore/versioning.html#load-manifest-files
 */
final class VersionedAssetsTwigExtension extends AbstractExtension
{
    public function __construct(
        private JsonManifestVersionStrategy $manifest,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this->manifest, 'applyVersion']),
        ];
    }
}
