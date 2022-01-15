<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Session;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Session\DatabaseSessionHandler;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Database\Models\Session as SessionTable;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Testing\withDatabaseSessionHandler;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Integration tests for the session service.
 */
// TODO : This whole test needs a rewrite using Mockery, not integration. Injection should be preferred for *SessionHandler.
//        The service itself should be tested in a separate test case and focus only on the logic used to determine which Handler is used.
class SessionDatabaseHandlerTest extends TestCase
{
    use RefreshDatabase;
    use withDatabaseSessionHandler;

    protected ConnectionInterface $connection;
    protected Config $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->refreshDatabase();

        // Set service alias
        $this->connection = $this->ci->get(Capsule::class)->connection();
        $this->config = $this->ci->get(Capsule::class);
    }

    /**
     * Test session table connection & existence
     */
    // TODO : Require Migration definitions
    /*public function testSessionTable()
    {
        $table = $this->config->get('session.database.table');

        // Check connection is ok and returns what's expected from DatabaseSessionHandler
        $this->assertInstanceOf(ConnectionInterface::class, $this->connection);
        $this->assertInstanceOf(\Illuminate\Database\Query\Builder::class, $this->connection->table($table));

        // Check table exist
        $this->assertTrue($this->connection->getSchemaBuilder()->hasTable($table));
    }*/

    /**
     * @depends testSessionTable
     */
    // TODO : Require Migration definitions
    /*public function testSessionWrite()
    {
        // Define random session ID
        // TODO : Use md5 of datetime to avoid duplicate
        $session_id = 'test' . rand(1, 100000);

        // Make sure db is empty at first
        $this->assertEquals(0, SessionTable::count());
        $this->assertNull(SessionTable::find($session_id));

        // Get handler
        $handler = new DatabaseSessionHandler($this->connection, $this->config->get('session.database.table'), $this->config->get('session.minutes'));

        // Write session
        // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Session/DatabaseSessionHandler.php#L132
        $this->assertTrue($handler->write($session_id, 'foo'));

        // Closing the handler does nothing anyway
        // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Session/DatabaseSessionHandler.php#L78
        $this->assertTrue($handler->close());

        // Read session
        // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Session/DatabaseSessionHandler.php#L86-L101
        $this->assertSame('foo', $handler->read($session_id));

        // Check manually that the file has been written
        $this->assertNotEquals(0, SessionTable::count());
        $this->assertNotNull(SessionTable::find($session_id));
        $this->assertSame(base64_encode('foo'), SessionTable::find($session_id)->payload);

        // Destroy session
        // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Session/DatabaseSessionHandler.php#L256
        $this->assertTrue($handler->destroy($session_id));

        // Check db to make sure it's gone
        $this->assertEquals(0, SessionTable::count());
        $this->assertNull(SessionTable::find($session_id));
    }*/

    /**
     * Simulate session service with database handler.
     * We can't use the real service as it is created before we can even setup
     * the in-memory database with the basic table we need
     *
     * @depends testSessionWrite
     */
    // TODO : Require Migration definitions
    /*public function testUsingSessionDouble()
    {

        $this->ci->get(Session::class)->destroy();

        $handler = new DatabaseSessionHandler($this->connection, $this->config->get('session.database.table'), $this->config->get('session.minutes'));
        $session = new Session($handler, $this->config->get('session'));

        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(DatabaseSessionHandler::class, $session->getHandler());
        $this->assertSame($handler, $session->getHandler());

        $this->sessionTests($session);
    }*/

    /**
     * @depends testUsingSessionDouble
     */
    // TODO : Require Migration definitions
    /*public function testUsingSessionService()
    {
        // Reset CI Session
        $this->useDatabaseSessionHandler();

        // Make sure config is set
        $this->sessionTests($this->ci->get(Session::class));
    }*/

    /**
     * @param Session $session
     */
    protected function sessionTests(Session $session)
    {
        // Make sure session service have correct instance
        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(DatabaseSessionHandler::class, $session->getHandler());

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

        // Make sure db was filled with something
        $this->assertNotEquals(0, SessionTable::count());
        $this->assertNotNull(SessionTable::find($session_id));
    }
}
