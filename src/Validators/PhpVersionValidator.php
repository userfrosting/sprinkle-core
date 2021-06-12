<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Validators;

use Composer\Semver\Semver;
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;

/**
 * Helper trait to check PHP version dependencies.
 */
class PhpVersionValidator extends AbstractVersionValidator
{
    protected string $message = 'UserFrosting requires PHP with a version that satisfies "%s", but found %s. Check the documentation for more details.';

    /**
     * Returns system php version.
     * Handle non semver compliant version of PHP returned by some OS.
     *
     * @see https://github.com/composer/semver/issues/125
     *
     * @return string
     */
    public function getInstalled(): string
    {
        $version = preg_replace('#^([^~+-]+).*$#', '$1', $this->installed);

        return $version;
    }
}
