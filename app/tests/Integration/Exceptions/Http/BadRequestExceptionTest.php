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
use UserFrosting\Sprinkle\Core\Exceptions\Http\BadRequestException;

/**
 * Tests AccountException
 */
class BadRequestExceptionTest extends TestCase
{
    public function testEvent(): void
    {
        $e = new BadRequestException();

        $this->assertSame('foo', $e->setTitle('foo')->getTitle());
        $this->assertSame('bar', $e->setDescription('bar')->getDescription());
        $this->assertSame('pages/error/error.html.twig', $e->getTemplate());
        $this->assertSame(400, $e->getCode());
    }
}
