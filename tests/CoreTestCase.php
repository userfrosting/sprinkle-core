<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests;

use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Testing\TestCase;

/**
 * Test case with Core as main sprinkle
 */
class CoreTestCase extends TestCase
{
    protected string $mainSprinkle = Core::class;
}
