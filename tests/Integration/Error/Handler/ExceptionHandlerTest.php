<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Integration\Error\Handler;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use UserFrosting\Sprinkle\Core\Error\Handler\ExceptionHandler;

class ExceptionHandlerTest extends TestCase
{
    /**
     * Test ExceptionHandler constructor
     */
    public function testConstructor()
    {
        $handler = new ExceptionHandler($this->getCi(), $this->getRequest(), $this->getResponse(), $this->getException(), false);
        $this->assertInstanceOf(ExceptionHandler::class, $handler);
    }

    /**
     * @return ContainerInterface
     */
    protected function getCi()
    {
        $ci = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ci->config = ['site.debug.ajax' => false];

        return $ci;
    }

    /**
     * @return ServerRequestInterface
     */
    protected function getRequest()
    {
        return $this->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return ResponseInterface
     */
    protected function getResponse()
    {
        return $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return RuntimeException
     */
    protected function getException()
    {
        return new RuntimeException('This is my exception');
    }
}
