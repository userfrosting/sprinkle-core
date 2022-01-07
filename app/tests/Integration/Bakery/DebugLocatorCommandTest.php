<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use UserFrosting\Sprinkle\Core\Bakery\DebugLocatorCommand;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Test DebugLocatorCommand
 *
 * Warning: As with most bakery command testing, this test make sure all code
 * is executed and doesn't throw errors, but the actual display is not tested.
 */
class DebugLocatorCommandTest extends CoreTestCase
{
    public function testCommand(): void
    {
        // Set stub CI and run command
        $ci = ContainerStub::create();
        $ci->set(ResourceLocatorInterface::class, $this->ci->get(ResourceLocatorInterface::class));

        /** @var DebugLocatorCommand */
        $command = $ci->get(DebugLocatorCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
    }
}