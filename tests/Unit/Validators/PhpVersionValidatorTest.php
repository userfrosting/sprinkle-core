<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Validators;

use UserFrosting\Sprinkle\Core\Validators\PhpVersionValidator;

/**
 * Unit tests for PhpVersionValidator trait.
 */
class PhpVersionValidatorTest extends AbstractVersionValidatorTester
{
    protected string $required = '^7.3 | ^8.0';

    protected string $validator = PhpVersionValidator::class;

    /**
     * PHP version provider.
     *
     * @return array [version, sanitized, valid]
     */
    public function versionProvider(): array
    {
        return [
            ['7.2.3', '7.2.3', false],
            ['7.3.14', '7.3.14', true],
            ['7.3', '7.3', true],
            ['7.4', '7.4', true],
            ['7.4.13', '7.4.13', true],
            ['8.0.3', '8.0.3', true],
            ['7.4.34-18+ubuntu20.04.1+deb.sury.org+1', '7.4.34', true],
        ];
    }
}
