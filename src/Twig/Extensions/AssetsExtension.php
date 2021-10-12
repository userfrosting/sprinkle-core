<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use UserFrosting\Assets\Assets;
use UserFrosting\Assets\AssetsTemplatePlugin;

/**
 * Extends Twig functionality for the Core sprinkle.
 */
// TODO : Test this...
class AssetsExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param Assets $assets The assets service
     */
    public function __construct(
        protected Assets $assets,
    ) {
    }

    /**
     * Adds Twig global variables `site` and `assets`.
     *
     * @return array[mixed]
     */
    public function getGlobals(): array
    {
        return [
            'assets' => new AssetsTemplatePlugin($this->assets),
        ];
    }
}
