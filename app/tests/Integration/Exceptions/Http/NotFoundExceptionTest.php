<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Exceptions\Http;

use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Account\Exceptions\AccountException;
use UserFrosting\Sprinkle\Core\Exceptions\Http\NotFoundException;

/**
 * Tests AccountException
 */
class NotFoundExceptionTest extends TestCase
{
    public function testEvent(): void
    {
        $e = new NotFoundException();

        $this->assertSame('ERROR.404.TITLE', $e->getTitle());
        $this->assertSame('ERROR.404.DESCRIPTION', $e->getDescription());
        $this->assertSame(404, $e->getCode());
    }
}
