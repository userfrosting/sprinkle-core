<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Util;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Util\ClassRepository\AbstractClassRepository;
use UserFrosting\Sprinkle\Core\Util\ClassRepository\ClassRepositoryInterface;
use UserFrosting\Support\Exception\NotFoundException;

class ClassRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testConstruct(): ClassRepositoryInterface
    {
        $repository = new TestClassRepository();
        $this->assertInstanceOf(ClassRepositoryInterface::class, $repository);

        return $repository;
    }

    /**
     * @depends testConstruct
     */
    public function testGetAll(ClassRepositoryInterface $repository): void
    {
        $classes = $repository->all();

        $this->assertIsArray($classes);
        $this->assertCount(2, $classes);
        $this->assertInstanceOf(StubClassA::class, $classes[0]);
        $this->assertInstanceOf(StubClassB::class, $classes[1]);
    }

    /**
     * @depends testConstruct
     * @depends testGetAll
     */
    public function testList(ClassRepositoryInterface $repository): void
    {
        $this->assertSame([
            StubClassA::class,
            StubClassB::class,
        ], $repository->list());
    }

    /**
     * @depends testConstruct
     * @depends testList
     */
    public function testHas(ClassRepositoryInterface $repository): void
    {
        $this->assertTrue($repository->has(StubClassA::class));
        $this->assertFalse($repository->has(StubClassC::class));
    }

    /**
     * @depends testConstruct
     * @depends testHas
     */
    public function testGet(ClassRepositoryInterface $repository): void
    {
        $class = $repository->get(StubClassA::class);
        $this->assertInstanceOf(StubClassA::class, $class);
    }

    /**
     * @depends testConstruct
     * @depends testHas
     */
    public function testGetWithNotFound(ClassRepositoryInterface $repository): void
    {
        $this->expectException(NotFoundException::class);
        $repository->get(StubClassC::class);
    }
}

class TestClassRepository extends AbstractClassRepository
{
    public function all(): array
    {
        return [
            new StubClassA(),
            new StubClassB(),
        ];
    }
}

class StubClassA
{
}

class StubClassB
{
}
