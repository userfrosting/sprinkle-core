<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Bakery\MigrateResetHardCommand;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Testing\BakeryTester;

/**
 * MigrateResetCommand tests
 */
class MigrateResetHardCommandTest extends CoreTestCase
{
    use TestDatabase;

    /**
     * Setup migration instances used for all tests
     */
    public function setUp(): void
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
    }

    public function testPretendHardReset(): void
    {
        // Get and run command
        $command = $this->ci->get(MigrateResetHardCommand::class);
        $result = BakeryTester::runCommand($command, input: ['--pretend' => true]);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $display = $result->getDisplay();
        $this->assertStringContainsString('Running migrate:reset:hard in pretend mode', $display);
        $this->assertStringContainsString('No tables found', $display);

        // Create a table
        $schema = $this->ci->get(Builder::class);
        $this->assertFalse($schema->hasTable('test'));
        $schema->create('test', function (Blueprint $table) {
            $table->id();
            $table->string('foo');
        });
        $this->assertTrue($schema->hasTable('test'));

        // Run command again
        $result = BakeryTester::runCommand($command, input: ['--pretend' => true]);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $display = $result->getDisplay();
        $this->assertStringContainsString('Running migrate:reset:hard in pretend mode', $display);
        $this->assertStringContainsString('Dropping table `test`...', $display);
        $this->assertStringContainsString('drop table "test"', $display);
        $this->assertTrue($schema->hasTable('test'));

        // Actually drop the table now
        $result = BakeryTester::runCommand($command, userInput: ['y']);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $display = $result->getDisplay();
        $this->assertStringContainsString('Dropping table `test`...', $display);
        $this->assertStringContainsString('Do you really wish to continue ?', $display);
        $this->assertStringContainsString('Hard reset successful', $display);
        $this->assertFalse($schema->hasTable('test'));
    }
}
