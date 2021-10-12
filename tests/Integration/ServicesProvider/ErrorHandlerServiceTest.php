<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerManager;

/**
 * Integration tests for `errorHandler` service.
 * Check to see if service returns what it's supposed to return
 */
// TODO : Disabled for now, requires re-implementation of the service.
class ErrorHandlerServiceTest extends TestCase
{
    /*public function testService()
    {
        $this->assertInstanceOf(ExceptionHandlerManager::class, $this->ci->errorHandler);
    }*/

    /**
     * @depends testService
     */
    /*public function testphpErrorHandlerService()
    {
        $this->assertInstanceOf(ExceptionHandlerManager::class, $this->ci->phpErrorHandler);
    }*/
}
