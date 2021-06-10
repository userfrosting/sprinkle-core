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
use UserFrosting\Sprinkle\Core\Validators\PhpVersionValidator;

/**
 * Unit tests for PhpVersionValidator trait.
 */
class PhpPhpVersionValidatorTest extends AbstractVersionValidatorTester
{
    protected string $required = '^7.3 | ^8.0';
    protected string $recommended = '^8.0';

    /**
     * @dataProvider versionProvider
     *
     * @param string $version
     * @param string $sanitized
     * @param bool   $valid
     */
    public function testValidator(string $version, string $sanitized, bool $valid): void
    {
        $validator = new PhpVersionValidator($version, $this->required, $this->recommended);
        
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
     * @dataProvider versionProvider
     *
     * @param string $version
     * @param string $sanitized
     * @param bool   $valid
     */
    public function testValidateDeprecation(string $version, string $sanitized, bool $valid, bool $deprecated): void
    {        
        $validator = new PhpVersionValidator($version, $this->required, $this->recommended);

        // Assert validatePhpDeprecation
        if (!$deprecated) {
            $this->assertTrue($validator->validateDeprecation());
        } else {
            try {
                $validator->validateDeprecation();
            } catch (VersionCompareException $e) {
                $this->assertSame($validator->getRecommended(), $e->getConstraint());
                $this->assertSame($sanitized, $e->getVersion());

                return;
            }

            $this->fail();
        }
    }

    /**
     * PHP version provider.
     *
     * @return array [version, sanitized, valid, deprecated]
     */
    public function versionProvider(): array
    {
        return [
            ['7.2.3', '7.2.3', false, true],
            ['7.3.14', '7.3.14', true, true],
            ['7.3', '7.3', true, true],
            ['7.4', '7.4', true, true],
            ['7.4.13', '7.4.13', true, true],
            ['8.0.3', '8.0.3', true, false],
            ['7.4.34-18+ubuntu20.04.1+deb.sury.org+1', '7.4.34', true, true],
        ];
    }
}
