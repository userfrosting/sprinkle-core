<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Sprinkle\Recipe;

/**
 * Sprinkle Twig Extensions definition Interface.
 */
interface TwigExtensionRecipe
{
    /**
     * Return an array of all registered Twig Extensions.
     *
     * @return \Twig\Extension\ExtensionInterface[]
     */
    public static function getTwigExtensions(): array;
}
