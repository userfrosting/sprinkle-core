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
 * Helper trait to check PHP deprecation.
 */
class PhpDeprecationValidator extends PhpVersionValidator
{
    protected string $message = 'UserFrosting recommend a PHP version that satisfies "%s". While your PHP version (%s) is still supported by UserFrosting, we recommend upgrading as your current version will soon be unsupported. See http://php.net/supported-versions.php for more info.';
}
