<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Util;

use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Sprinkle\Core\Util\Util;

class UtilTest extends CoreTestCase
{
    public function testRandomPhrase(): void
    {
        $result = Util::randomPhrase(2);
        $this->assertNotSame('', $result);
        $this->assertStringContainsString('-', $result);
    }

    public function testRandomPhraseEmptyResult(): void
    {
        // Use impossible param :)
        $result = Util::randomPhrase(4, 1);
        $this->assertSame('', $result);
    }
}
