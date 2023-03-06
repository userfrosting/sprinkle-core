<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Core\Bakery\ClearCacheCommand;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Sprinkle\Core\Twig\CacheHelper;
use UserFrosting\Testing\BakeryTester;

/**
 * Test ClearCacheCommand
 */
class ClearCacheCommandTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    public function testCommand(): void
    {
        // Setup mocks
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('flush')->once()
            ->getMock();
        $this->ci->set(Cache::class, $cache);

        $cacheHelper = Mockery::mock(CacheHelper::class)
            ->shouldReceive('clearCache')->once()->andReturn(true)
            ->getMock();
        $this->ci->set(CacheHelper::class, $cacheHelper);

        /** @var ClearCacheCommand */
        $command = $this->ci->get(ClearCacheCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Cache cleared', $result->getDisplay());
    }

    public function testCommandForFailure(): void
    {
        // Setup mocks
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('flush')->once()
            ->getMock();
        $this->ci->set(Cache::class, $cache);

        $cacheHelper = Mockery::mock(CacheHelper::class)
            ->shouldReceive('clearCache')->once()->andReturn(false)
            ->getMock();
        $this->ci->set(CacheHelper::class, $cacheHelper);

        /** @var ClearCacheCommand */
        $command = $this->ci->get(ClearCacheCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Failed to clear Twig cached data', $result->getDisplay());
    }
}
