<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Sprinkle\Recipe;

/**
 * Sprinkle Resource Steams definition Interface.
 */
interface LocatorRecipe
{
    /**
     * Return an array of all locator Resource Steams to register with locator.
     *
     * @return \UserFrosting\UniformResourceLocator\ResourceStreamInterface[]
     */
    public static function getResourceStreams(): array;
}
