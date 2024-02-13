<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Sprinkle\Recipe;

/**
 * Sprinkle Composer package recipe.
 *
 * This recipe is used to associate the sprinkle with a Composer package. The
 * package name is used to fetch information about the sprinkle from Composer,
 * such as the installed version.
 */
interface ComposerRecipe
{
    /**
     * Return the sprinkle Composer package (vendor/name).
     *
     * @return string
     */
    public function getComposerPackage(): string;
}
