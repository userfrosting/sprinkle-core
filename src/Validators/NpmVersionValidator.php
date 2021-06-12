<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Validators;

/**
 * Helper trait to check NPM version dependencies.
 */
class NpmVersionValidator extends AbstractVersionValidator
{
    protected string $message = 'UserFrosting requires NPM with a version that satisfies "%s", but found %s. Check the documentation for more details.';
}
