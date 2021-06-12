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
 * Helper trait to check version dependencies.
 */
class AbstractVersionValidator
{
    protected string $message = '';

    public function __construct(
        protected string $installed,
        protected string $required,
    ) {
    }

    /**
     * Check the minimum version requirement of X installed.
     *
     * @throws VersionCompareException If constraint version is not matched.
     *
     * @return true Version is valid
     */
    public function validate(): bool
    {
        $version = $this->getInstalled();
        $constraint = $this->getConstraint();

        if (!Semver::satisfies($version, $constraint)) {
            $message = sprintf($this->message, $constraint, $version);
            $exception = new VersionCompareException($message);
            $exception->setConstraint($constraint)->setVersion($version);

            throw $exception;
        }

        return true;
    }

    /**
     * Returns system Node version.
     *
     * @return string
     */
    public function getInstalled(): string
    {
        return trim($this->installed);
    }

    /**
     * Returns the required Node semver range.
     *
     * @return string
     */
    public function getConstraint(): string
    {
        return $this->required;
    }
}
