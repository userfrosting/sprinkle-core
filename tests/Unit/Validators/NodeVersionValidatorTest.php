<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Validators;

use UserFrosting\Sprinkle\Core\Validators\NodeVersionValidator;

/**
 * Unit tests for NodeVersionValidator trait.
 */
class NodeNodeVersionValidatorTest extends AbstractVersionValidatorTester
{
    protected string $required = '^12.17.0 || >=14.0.0';

    protected string $validator = NodeVersionValidator::class;

    /**
     * Node version provider.
     *
     * @return array [version, sanitized, valid]
     */
    public function versionProvider(): array
    {
        return [
            ['v12.18.1', 'v12.18.1', true],
            ['v13.12.3', 'v13.12.3', false],
            ['v14.0.0 ', 'v14.0.0', true], // Test trim here
        ];
    }
}
