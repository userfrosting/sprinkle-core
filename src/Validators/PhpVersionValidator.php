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
    protected string $entity = 'PHP';

    public function __construct(
        protected string $installed,
        protected string $required,
        protected string $recommended,
    ) {
    }

    /**
     * Check if php version is deprecated.
     *
     * @throws VersionCompareException If constraint version is not matched.
     *
     * @return true Version is valid
     */
    public function validateDeprecation(): bool
    {
        $phpVersion = $this->getInstalled();
        $constraint = $this->getRecommended();

        if (!Semver::satisfies($phpVersion, $constraint)) {
            $message = 'UserFrosting recommend a PHP version that satisfies "' . $constraint . '". While your PHP version (' . $phpVersion . ') is still supported by UserFrosting, we recommend upgrading as your current version will soon be unsupported. See http://php.net/supported-versions.php for more info.';
            $exception = new VersionCompareException($message);
            $exception->setConstraint($constraint)->setVersion($phpVersion);

            throw $exception;
        }

        return true;
    }

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

    /**
     * Returns the recommended PHP semver range.
     *
     * @return string
     */
    public function getRecommended(): string
    {
        return $this->recommended;
    }
}
