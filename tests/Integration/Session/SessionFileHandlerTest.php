<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Session;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\FileSessionHandler;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Integration tests for the session service.
 */
// TODO : This whole test needs a rewrite using Mockery, not integration. Injection should be preferred for *SessionHandler.
//        The service itself should be tested in a separate test case and focus only on the logic used to determine which Handler is used.
class SessionFileHandlerTest extends TestCase
{
    protected ResourceLocatorInterface $locator;

    public function setUp(): void
    {
        parent::setUp();

        // Set service alias
        $this->setTestLocator();
    }

    /**
     * Test FileSessionHandler works with our locator
     */
    // TODO : Needs to setup a tes location for the locator
    public function testSessionWrite()
    {
        $fs = new Filesystem();

        // Define random session ID
        // TODO : Use md5 of datetime to avoid duplicate
        $session_id = 'test' . rand(1, 100000);

        // Get session dir
        $session_dir = $this->locator->findResource('session://');

        // Define session filename
        $session_file = "$session_dir/$session_id";

        // Delete existing file to prevent false positive
        $fs->delete($session_file);
        $this->assertFalse($fs->exists($session_file));

        // Get handler
        $handler = new FileSessionHandler($fs, $session_dir, 120);

        // Write session
        // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Session/FileSessionHandler.php#L83
        // write() ==> $this->files->put($this->path.'/'.$sessionId, $data, true);
        $this->assertTrue($handler->write($session_id, 'foo'));

        // Closing the handler does nothing anyway
        // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Session/FileSessionHandler.php#L61
        // close() ==> return true;
        $this->assertTrue($handler->close());

        // Read session
        // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Session/FileSessionHandler.php#L71
        // read() ==> return $this->files->get($path, true);
        $this->assertSame('foo', $handler->read($session_id));

        // Check manually that the file has been written
        $this->assertTrue($fs->exists($session_file));
        $this->assertSame('foo', $fs->get($session_file));

        // Destroy session
        // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Session/FileSessionHandler.php#L93
        // destroy() ==> $this->files->delete($this->path.'/'.$sessionId);
        $this->assertTrue($handler->destroy($session_id));

        // Check filesystem to make sure it's gone
        $this->assertFalse($fs->exists($session_file));
    }

    /**
     * @depends testSessionWrite
     */
    // TODO : Require session service
    public function testUsingSessionDouble()
    {
        $this->ci->get(Session::class)->destroy();
        $this->sessionTests($this->getSession());
    }

    /**
     * @depends testUsingSessionDouble
     */
    // TODO : This whole test needs a rewrite using Mockery.
    /*public function testUsingSessionService()
    {
        // Force test to use database session handler
        putenv('TEST_SESSION_HANDLER=file');

        // Refresh app to use new setup
        $this->ci->get(Session::class)->destroy();
        $this->refreshApplication();

        // Needs to reset the locator stream
        $this->setTestLocator();

        // Check setting is ok
        $this->assertSame('file', $this->ci->get(Config::class)->get('session.handler'));

        // Make sure config is set
        $this->sessionTests($this->ci->get(Session::class));

        // Unset the env when test is done to avoid conflict
        putenv('TEST_SESSION_HANDLER');
    }*/

    /**
     * Simulate session service with database handler.
     * We can't use the real service as it is created before we can even setup
     * the in-memory database with the basic table we need
     *
     * @return Session
     */
    protected function getSession()
    {
        $config = $this->ci->get(Config::class);
        $locator = $this->ci->get(ResourceLocatorInterface::class);

        $fs = new Filesystem();
        $handler = new FileSessionHandler($fs, $locator->findResource('session://'), 120);
        $session = new Session($handler, $config['session']);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(FileSessionHandler::class, $session->getHandler());
        $this->assertSame($handler, $session->getHandler());

        return $session;
    }

    /**
     * @param Session $session
     */
    protected function sessionTests(Session $session)
    {
        // Make sure session service have correct instance
        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(FileSessionHandler::class, $session->getHandler());

        // Destroy previously defined session
        $session->destroy();

        // Start new one and validate status
        $this->assertSame(PHP_SESSION_NONE, $session->status());
        $session->start();
        $this->assertSame(PHP_SESSION_ACTIVE, $session->status());

        // Get id
        $session_id = $session->getId();

        // Set something to the session
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));

        // Close session to initiate write
        session_write_close();

        // Make sure file was filled with something
        $session_dir = $this->locator->findResource('session://');
        $session_file = "$session_dir/$session_id";

        $fs = new Filesystem();
        $this->assertTrue($fs->exists($session_file));
        $this->assertSame('foo|s:3:"bar";', $fs->get($session_file));
    }

    protected function setTestLocator(): void
    {
        // Set service alias
        $this->locator = $this->ci->get(ResourceLocatorInterface::class);
        $this->locator->removeStream('session')->registerStream('session', '', __DIR__ . '/data', true);
    }
}
