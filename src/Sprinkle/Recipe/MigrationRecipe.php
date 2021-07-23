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
 * Sprinkle Migration definition Interface.
 */
interface MigrationRecipe
{
    /**
     * Return an array of all registered Migrations.
     *
     * @return \UserFrosting\Sprinkle\Core\Database\MigrationInterface[]
     */
    public static function getMigrations(): array;
}
