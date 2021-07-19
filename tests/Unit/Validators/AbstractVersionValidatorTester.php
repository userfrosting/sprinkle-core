<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;

/**
 * Unit tests for *VersionValidator trait.
 */
abstract class AbstractVersionValidatorTester extends TestCase
{
    protected string $required;

    protected string $validator;

    /**
     * @dataProvider versionProvider
     *
     * @param string $version
     * @param string $sanitized
     * @param bool   $valid
     */
    public function testValidator(string $version, string $sanitized, bool $valid): void
    {
        $validator = new $this->validator($version, $this->required);

        // Assert installed version is sanitized
        $this->assertSame($sanitized, $validator->getInstalled());

        // Assert validate function
        if ($valid) {
            $this->assertTrue($validator->validate());
        } else {
            try {
                $validator->validate();
            } catch (VersionCompareException $e) {
                $this->assertSame($validator->getConstraint(), $e->getConstraint());
                $this->assertSame($sanitized, $e->getVersion());

                return;
            }

            $this->fail();
        }
    }

    /**
     * Version provider.
     *
     * @return array [version, sanitized, valid]
     */
    abstract public function versionProvider(): array;
}
