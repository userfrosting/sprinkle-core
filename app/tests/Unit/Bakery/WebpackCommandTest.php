<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Bakery;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use phpmock\MockBuilder;
use phpmock\mockery\PHPMockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use UserFrosting\Sprinkle\Core\Bakery\WebpackCommand;
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;
use UserFrosting\Sprinkle\Core\Validators\NodeVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\NpmVersionValidator;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\Testing\ContainerStub;

/**
 * Test `webpack` command.
 *
 * Warning : This test doesn't fully test the output format.
 */
class WebpackCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCommand(): void
    {
        // Mock built-in error_get_last
        $reflection_class = new ReflectionClass(WebpackCommand::class);
        $namespace = $reflection_class->getNamespaceName();
        PHPMockery::mock($namespace, 'getcwd')->andReturn('foo/');
        PHPMockery::mock($namespace, 'file_exists')->andReturn(true);
        PHPMockery::mock($namespace, 'passthru')->andReturn(null);

        // Set Validator mock
        $node = Mockery::mock(NodeVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();
        $npm = Mockery::mock(NpmVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();

        // Set mock in CI and run command
        $ci = ContainerStub::create();
        $ci->set(NodeVersionValidator::class, $node);
        $ci->set(NpmVersionValidator::class, $npm);

        /** @var WebpackCommand */
        $command = $ci->get(WebpackCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Assets Compiled', $result->getDisplay());
    }

    public function testCommandWithMissingFiles(): void
    {
        // Mock built-in error_get_last
        $reflection_class = new ReflectionClass(WebpackCommand::class);
        $namespace = $reflection_class->getNamespaceName();
        PHPMockery::mock($namespace, 'getcwd')->andReturn('./foo');
        PHPMockery::mock($namespace, 'file_exists')->andReturn(false);

        // Set Validator mock
        $node = Mockery::mock(NodeVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();
        $npm = Mockery::mock(NpmVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();

        // Set mock in CI and run command
        $ci = ContainerStub::create();
        $ci->set(NodeVersionValidator::class, $node);
        $ci->set(NpmVersionValidator::class, $npm);

        /** @var WebpackCommand */
        $command = $ci->get(WebpackCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('./foo/package.json not found. Skipping.', $result->getDisplay());
        $this->assertStringContainsString('./foo/webpack.config.js not found. Skipping.', $result->getDisplay());
    }

    public function testCommandWithErrorInGetcwd(): void
    {
        // Mock built-in error_get_last
        $reflection_class = new ReflectionClass(WebpackCommand::class);
        $namespace = $reflection_class->getNamespaceName();
        PHPMockery::mock($namespace, 'getcwd')->andReturn(false);

        // Set Validator mock
        $node = Mockery::mock(NodeVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();
        $npm = Mockery::mock(NpmVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();

        // Set mock in CI and run command
        $ci = ContainerStub::create();
        $ci->set(NodeVersionValidator::class, $node);
        $ci->set(NpmVersionValidator::class, $npm);

        /** @var WebpackCommand */
        $command = $ci->get(WebpackCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Error getting working directory', $result->getDisplay());
    }

    public function testCommandWithNodeError(): void
    {
        // Set Validator mock
        $node = Mockery::mock(NodeVersionValidator::class)
            ->shouldReceive('validate')->andThrow(new VersionCompareException())
            ->getMock();
        $npm = Mockery::mock(NpmVersionValidator::class);

        // Set mock in CI and run command
        $ci = ContainerStub::create();
        $ci->set(NodeVersionValidator::class, $node);
        $ci->set(NpmVersionValidator::class, $npm);

        /** @var WebpackCommand */
        $command = $ci->get(WebpackCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(1, $result->getStatusCode());
    }

    public function testCommandWithNpmError(): void
    {
        // Set Validator mock
        $node = Mockery::mock(NodeVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();
        $npm = Mockery::mock(NpmVersionValidator::class)
        ->shouldReceive('validate')->andThrow(new VersionCompareException())
        ->getMock();

        // Set mock in CI and run command
        $ci = ContainerStub::create();
        $ci->set(NodeVersionValidator::class, $node);
        $ci->set(NpmVersionValidator::class, $npm);

        /** @var WebpackCommand */
        $command = $ci->get(WebpackCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(1, $result->getStatusCode());
    }

    public function testCommandWithNpmPassthruError(): void
    {
        // Mock built-in error_get_last
        $reflection_class = new ReflectionClass(WebpackCommand::class);
        $namespace = $reflection_class->getNamespaceName();
        PHPMockery::mock($namespace, 'getcwd')->andReturn('foo/');
        PHPMockery::mock($namespace, 'file_exists')->andReturn(true);

        // Use `MockBuilder` for more control
        $builder = new MockBuilder();
        $builder->setNamespace($namespace)
                ->setName('passthru')
                ->setFunction(
                    function (string $command, int &$exitCode) {
                        $exitCode = 1;
                    }
                );
        $mock = $builder->build();
        $mock->enable();

        // Set Validator mock
        $node = Mockery::mock(NodeVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();
        $npm = Mockery::mock(NpmVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();

        // Set mock in CI and run command
        $ci = ContainerStub::create();
        $ci->set(NodeVersionValidator::class, $node);
        $ci->set(NpmVersionValidator::class, $npm);

        /** @var WebpackCommand */
        $command = $ci->get(WebpackCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('npm dependency installation has failed', $result->getDisplay());

        // Disable mock manually
        $mock->disable();
    }

    public function testCommandWithNodePassthruError(): void
    {
        // Mock built-in error_get_last
        $reflection_class = new ReflectionClass(WebpackCommand::class);
        $namespace = $reflection_class->getNamespaceName();
        PHPMockery::mock($namespace, 'getcwd')->andReturn('foo/');
        PHPMockery::mock($namespace, 'file_exists')->andReturn(true);

        // Use `MockBuilder` for more control
        $builder = new MockBuilder();
        $builder->setNamespace($namespace)
                ->setName('passthru')
                ->setFunction(
                    function (string $command, int &$exitCode) {
                        if ($command !== 'npm install') {
                            $exitCode = 1;
                        }
                    }
                );
        $mock = $builder->build();
        $mock->enable();

        // Set Validator mock
        $node = Mockery::mock(NodeVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();
        $npm = Mockery::mock(NpmVersionValidator::class)
            ->shouldReceive('validate')->andReturn(true)
            ->getMock();

        // Set mock in CI and run command
        $ci = ContainerStub::create();
        $ci->set(NodeVersionValidator::class, $node);
        $ci->set(NpmVersionValidator::class, $npm);

        /** @var WebpackCommand */
        $command = $ci->get(WebpackCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assert some output
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Webpack Encore run has failed', $result->getDisplay());

        // Disable mock manually
        $mock->disable();
    }
}
