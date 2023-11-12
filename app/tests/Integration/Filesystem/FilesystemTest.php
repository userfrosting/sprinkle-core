<?php

declare(strict_types=1);

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
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Filesystem\FilesystemManager;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

class FilesystemTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Setup test config
        $config = $this->ci->get(Config::class);
        $config->set('filesystems.disks.testing', [
            'driver' => 'local',
            'root'   => 'testing://',
            'url'    => 'files/testing/',
        ]);
        $config->set('filesystems.disks.testingDriver', [
            'driver' => 'localTest',
            'root'   => 'testing://testingDriver',
        ]);

        // Set up the locator stream in the testing directory
        /** @var ResourceLocatorInterface */
        $locator = $this->ci->get(ResourceLocatorInterface::class);
        $locator->addStream(new ResourceStream('testing', __DIR__ . '/storage/testing', true));
    }

    /**
     * Test the service and FilesystemManager
     */
    public function testService(): FilesystemAdapter
    {
        // Set the default filesystem to the testing disk
        $config = $this->ci->get(Config::class);
        $config->set('filesystems.default', 'testing');

        // Filesystem service will return an instance of FilesystemManger
        $filesystem = $this->ci->get(FilesystemManager::class);
        $this->assertInstanceOf(FilesystemManager::class, $filesystem);

        // Main aspect of our FilesystemManager is to adapt our config structure
        // to Laravel class we'll make sure here the forced config actually works
        $this->assertEquals('testing', $filesystem->getDefaultDriver());

        // The disk won't return a Manager, but an Adapter.
        $disk = $filesystem->disk('testing');
        $this->assertInstanceOf(FilesystemAdapter::class, $disk);

        return $disk;
    }

    public function testDefaultCloud(): void
    {
        // Set the default cloud to the testing disk
        $config = $this->ci->get(Config::class);
        $config->set('filesystems.cloud', 'testingDriver');
        $filesystem = $this->ci->get(FilesystemManager::class);
        $this->assertEquals('testingDriver', $filesystem->getDefaultCloudDriver());
    }

    /**
     * @param FilesystemAdapter $files
     * @depends testService
     */
    public function testAdapter(FilesystemAdapter $files): void
    {
        // Test "path", make sure the path is translated correctly via locator
        $this->assertEquals(__DIR__ . '/storage/testing/', $files->path(''));

        // Test basic "put"
        $this->assertTrue($files->put('file.txt', 'Something inside'));
        $this->assertStringEqualsFile('testing://file.txt', 'Something inside');

        // Test "exist" & "get"
        // We'll assume Laravel test covers the rest ;)
        $this->assertTrue($files->exists('file.txt'));
        $this->assertEquals('Something inside', $files->get('file.txt'));

        // We'll delete the test file now
        $this->assertTrue($files->delete('file.txt'));
        $this->assertFileDoesNotExist('testing://file.txt');
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
        $this->assertFileDoesNotExist('testing://file.txt');
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
            $locator = $this->ci->get(ResourceLocatorInterface::class);
            $config['root'] = $locator->findResource($config['root'], all: true);
            $adapter = new LocalAdapter($config['root']);

            return new Filesystem($adapter);
        });

        $disk = $filesystemManager->disk('testingDriver');
        $this->assertInstanceOf(FilesystemAdapter::class, $disk);

        // Make sure the path was set correctly
        $this->assertEquals(__DIR__ . '/storage/testing/testingDriver/', $disk->path(''));
    }

    public function testAddingDriver(): void
    {
        $filesystemManager = $this->ci->get(FilesystemManager::class);

        $adapter = new LocalAdapter(__DIR__ . '/storage/testing/testingDriver');
        $filesystem = new Filesystem($adapter);
        $driver = new FilesystemAdapter($filesystem);

        $filesystemManager->extend('localTest', function ($configService, $config) use ($driver) {
            return $driver;
        });

        $disk = $filesystemManager->disk('testingDriver');
        $this->assertInstanceOf(FilesystemAdapter::class, $disk);

        // Make sure the path was set correctly
        $this->assertEquals(__DIR__ . '/storage/testing/testingDriver/', $disk->path(''));
    }
}
