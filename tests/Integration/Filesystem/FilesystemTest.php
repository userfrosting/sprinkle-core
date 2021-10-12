<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Filesystem;

use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UserFrosting\Sprinkle\Core\Facades\Storage;
use UserFrosting\Sprinkle\Core\Filesystem\FilesystemManager;
// use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * FilesystemTest class.
 * Tests a basic filesystem.
 */
// TODO : Most could be moved to a Unit Test.
class FilesystemTest extends TestCase
{
    /** @var string Testing storage path */
    private string $testDir;

    /** @var string Test disk name */
    private string $testDisk = 'testing';

    protected Config $config;

    /**
     * Setup TestDatabase
     */
    public function setUp(): void
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        $this->config = $this->ci->get(Config::class);

        $this->testDir = $this->config->get("filesystems.disks.{$this->testDisk}.root");
    }

    /**
     * Test the service and FilesystemManager
     */
    public function testService(): FilesystemAdapter
    {
        // Force this test to use the testing disk
        $this->config->set('filesystems.default', $this->testDisk);

        // Filesystem service will return an instance of FilesystemManger
        $filesystem = $this->ci->get(FilesystemManager::class);
        $this->assertInstanceOf(FilesystemManager::class, $filesystem);

        // Main aspect of our FilesystemManager is to adapt our config structure
        // to Laravel class we'll make sure here the forced config actually works
        $this->assertEquals($this->testDisk, $filesystem->getDefaultDriver());

        // The disk won't return a Manager, but an Adapter.
        $disk = $filesystem->disk($this->testDisk);
        $this->assertInstanceOf(FilesystemAdapter::class, $disk);

        return $disk;
    }

    /**
     * @depends testService
     */
    // TODO : Requires reimplementation of Facade
    /*public function testFacade(): void
    {
        $this->assertInstanceOf(FilesystemAdapter::class, Storage::disk($this->testDisk));
    }*/

    /**
     * @param FilesystemAdapter $files
     * @depends testService
     */
    public function testAdapter(FilesystemAdapter $files): void
    {
        // Test basic "put"
        $this->assertTrue($files->put('file.txt', 'Something inside'));
        $this->assertStringEqualsFile($this->testDir . '/file.txt', 'Something inside');

        // Test "exist" & "get"
        // We'll assume Laravel test covers the rest ;)
        $this->assertTrue($files->exists('file.txt'));
        $this->assertEquals('Something inside', $files->get('file.txt'));

        // We'll delete the test file now
        $this->assertTrue($files->delete('file.txt'));
        $this->assertFileDoesNotExist($this->testDir . '/file.txt');
    }

    /**
     * @param FilesystemAdapter $files
     * @depends testService
     * NOTE : The `download` method was introduced in Laravel 5.5.
     * We'll need to enable this once we can upgrade to newer version of Laravel
     */
    // TODO : Should be good to add now
    /*public function testDownload(FilesystemAdapter $files): void
    {
        // We'll test the file download feature
        $response = $files->download('file.txt', 'hello.txt');
        $this->assertInstanceOf(StreamedResponse::class, $response);
        // $this->assertEquals('attachment; filename="hello.txt"', $response->headers->get('content-disposition'));
    }*/

    /**
     * @param FilesystemAdapter $files
     * @depends testService
     */
    public function testUrl(FilesystemAdapter $files): void
    {
        // Test the URL
        $this->assertTrue($files->put('file.txt', 'Blah!'));
        $url = $files->url('file.txt');
        $this->assertEquals('files/testing/file.txt', $url);
        $this->assertTrue($files->delete('file.txt'));
        $this->assertFileDoesNotExist($this->testDir . '/file.txt');
    }

    /**
     * Test to make sure we can still add custom adapter
     */
    public function testNonExistingAdapter(): void
    {
        $filesystemManager = $this->ci->get(FilesystemManager::class);

        // InvalidArgumentException
        $this->expectException('InvalidArgumentException');
        $filesystemManager->disk('testingDriver');
    }

    /**
     * @depends testNonExistingAdapter
     * @see https://github.com/thephpleague/flysystem/blob/13352d2303b67ecfc1306ef1fdb507df1a0fc79f/src/Adapter/Local.php#L47
     */
    public function testAddingAdapter(): void
    {
        $filesystemManager = $this->ci->get(FilesystemManager::class);

        $filesystemManager->extend('localTest', function ($configService, $config) {
            $adapter = new LocalAdapter($config['root']);

            return new Filesystem($adapter);
        });

        $disk = $filesystemManager->disk('testingDriver');
        $this->assertInstanceOf(FilesystemAdapter::class, $disk);

        // Make sure the path was set correctly
        $path = $disk->path('');
        $this->assertEquals(\UserFrosting\STORAGE_DIR . \UserFrosting\DS . 'testingDriver' . DIRECTORY_SEPARATOR, $path);
    }
}
