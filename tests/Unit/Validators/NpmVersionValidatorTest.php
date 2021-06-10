<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Validators;

use UserFrosting\Sprinkle\Core\Validators\NpmVersionValidator;

/**
 * Unit tests for NpmVersionValidator trait.
 */
class NpmNpmVersionValidatorTest extends AbstractVersionValidatorTester
{
    protected string $required = '>=6.14.4';

    protected string $validator = NpmVersionValidator::class;

    /**
     * Node version provider.
     *
     * @return array [version, sanitized, valid]
     */
    public function versionProvider(): array
    {
        return [
            [' 6.14.10 ', '6.14.10', true], // Trim
            ['6.14.4', '6.14.4', true],
            ['5.12.14', '5.12.14', false],
        ];
    }
}
