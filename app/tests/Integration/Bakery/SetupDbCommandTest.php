<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use phpmock\mockery\PHPMockery;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Bakery\Helper\DbParamTester;
use UserFrosting\Sprinkle\Core\Bakery\SetupDbCommand;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Test for SetupDbCommand (setup:db)
 */
class SetupDbCommandTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // Use test location
        $dbStream = new ResourceStream('database', shared: true);
        $envStream = new ResourceStream('sprinkles', path: 'env', shared: true);
        $locator = new ResourceLocator(__DIR__ . '/data');
        $locator->addStream($dbStream);
        $locator->addStream($envStream);
        $this->ci->set(ResourceLocatorInterface::class, $locator);
    }

    public function testEnvNotFound(): void
    {
        // Force locator return with Mockery
        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('getResource')->andReturn(null); // For databaseDrivers method
        $locator->shouldReceive('findResource')->andReturn(null);
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Run command and assert it fails
        /** @var SetupDbCommand */
        $command = $this->ci->get(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command);
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Could not find .env file', $result->getDisplay());
    }

    public function testCommandWithNoInput(): void
    {
        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->ci->get(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, input: ['--force' => true]);

        // Assertions
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Aborted.', $result->getDisplay());
    }

    public function testCommandWithExistingDb(): void
    {
        // Mock Capsule to manipulate the result
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->once()->andReturn(true)
            ->getMock();
        $this->ci->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->ci->get(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Database already setup.', $result->getDisplay());
    }

    /**
     * Warning : This test needs to be run BEFORE `testCommand`
     * @see https://github.com/php-mock/php-mock-mockery#restrictions
     */
    public function testCommandWithFailedTouch(): void
    {
        // Make sure database file doesn't exist yet
        $this->assertFileDoesNotExist(__DIR__ . '/data/database/database.sql');

        // Mock capsule to manipulate the result
        // This simulate the entered config IS valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andThrow(new PDOException())
            ->getMock();
        $this->ci->set(DbParamTester::class, $tester);

        // Mock touch function to fail
        $reflection_class = new ReflectionClass(SetupDbCommand::class);
        $namespace = $reflection_class->getNamespaceName();
        PHPMockery::mock($namespace, 'touch')->andReturn(false);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->ci->get(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, userInput: [
            '3',
            __DIR__ . '/data/database/database.sql',
        ]);

        // Assertions
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Unable to create database file', $result->getDisplay());
    }

    public function testCommand(): void
    {
        // Make sure database file doesn't exist yet
        $this->assertFileDoesNotExist(__DIR__ . '/data/database/database.sql');

        // Mock capsule to manipulate the result
        // This simulate the entered config IS valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andThrow(new PDOException())
            ->shouldReceive('test')->times(1)->andReturn(true)
            ->getMock();
        $this->ci->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->ci->get(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, userInput: [
            '3',
            __DIR__ . '/data/database/database.sql',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Database connection successful', $result->getDisplay());
        $this->assertStringContainsString('Database config successfully saved', $result->getDisplay());

        // Delete database file
        unlink(__DIR__ . '/data/database/database.sql');
        $this->assertFileDoesNotExist(__DIR__ . '/data/database/database.sql');
    }

    public function testCommandWithOptionsAndVerbose(): void
    {
        // Mock capsule to manipulate the result
        // This simulate the entered config is valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andReturn(true)
            ->getMock();
        $this->ci->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->ci->get(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, verbosity: OutputInterface::VERBOSITY_VERBOSE, input: [
            '--force'       => true,
            '--db_driver'   => 'mysql',
            '--db_name'     => 'database_name',
            '--db_host'     => 'localhost',
            '--db_port'     => '3306',
            '--db_user'     => 'database_user',
            '--db_password' => 'database_password',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Save path for database credentials', $result->getDisplay());
        $this->assertStringContainsString('Database connection successful', $result->getDisplay());
        $this->assertStringContainsString('Database config successfully saved', $result->getDisplay());
    }

    public function testCommandForBadNewConfig(): void
    {
        // Mock capsule to manipulate the result
        // This simulate the entered config is valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andThrow(new PDOException())
            ->getMock();
        $this->ci->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->ci->get(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, input: [
            '--force'       => true,
            '--db_driver'   => 'mysql',
            '--db_name'     => 'database_name',
            '--db_host'     => 'localhost',
            '--db_port'     => '3306',
            '--db_user'     => 'database_user',
            '--db_password' => 'database_password',
        ]);

        // Assertions
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Could not connect to the database', $result->getDisplay());
    }

    public function testCommandWithUserInput(): void
    {
        // Mock capsule to manipulate the result
        // This simulate the entered config is valid
        $tester = Mockery::mock(DbParamTester::class)
            ->shouldReceive('test')->times(1)->andReturn(true)
            ->getMock();
        $this->ci->set(DbParamTester::class, $tester);

        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->ci->get(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, input: ['--force' => true], userInput: [
            '0',
            'localhost',
            3306,
            'userfrosting',
            'userfrosting',
            'password',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Database connection successful', $result->getDisplay());
        $this->assertStringContainsString('Database config successfully saved', $result->getDisplay());
    }

    public function testCommandForBadDriver(): void
    {
        // Run command and assert result
        /** @var SetupDbCommand */
        $command = $this->ci->get(SetupDbCommand::class);
        $result = BakeryTester::runCommand($command, input: [
            '--force'       => true,
            '--db_driver'   => 'foo',
        ]);

        // Assertions
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Invalid database driver: foo', $result->getDisplay());
    }
}
