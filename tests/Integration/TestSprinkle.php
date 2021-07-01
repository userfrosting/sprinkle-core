<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration;

use UserFrosting\Sprinkle\SprinkleRecipe;

class TestSprinkle implements SprinkleRecipe
{
    /**
     * {@inheritdoc}
     */
    public static function getName(): string
    {
        return 'Test Sprinkle';
    }

    /**
     * {@inheritdoc}
     */
    public static function getPath(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public static function getBakeryCommands(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSprinkles(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getRoutes(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getServices(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMiddlewares(): array
    {
        return [];
    }
}
