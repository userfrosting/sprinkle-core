<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

/**
 * Bad class name exception.  Used when a class name is dynamically invoked, but the class does not exist.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class BadClassNameException extends \LogicException
{
    //
}