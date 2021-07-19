<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Validators;

use UserFrosting\Sprinkle\Core\Validators\PhpDeprecationValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpVersionValidator;

/**
 * Unit tests for PhpVersionValidator trait.
 */
class PhpDeprecationValidatorTest extends AbstractVersionValidatorTester
{
    protected string $required = '^8.0';

    protected string $validator = PhpDeprecationValidator::class;

    /**
     * PHP version provider.
     *
     * @return array [version, sanitized, deprecated]
     */
    public function versionProvider(): array
    {
        return [
            ['7.4.13', '7.4.13', false],
            ['8.0.3', '8.0.3', true],
        ];
    }
}
