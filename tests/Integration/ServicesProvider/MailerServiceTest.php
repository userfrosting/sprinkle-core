<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Log\MailLogger;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\ServicesProvider\MailService;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Testing\ContainerStub;

/**
 * Integration tests for `mailer` service.
 * Check to see if service returns what it's supposed to return
 */
class MailerServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testService()
    {
        // Create container with provider to test
        $provider = new MailService();
        $ci = ContainerStub::create($provider->register());

        // TODO : We test the service implementation here. This shouldn't be necessary.
        $mailConfig = [
            'mailer'          => 'smtp',
            'host'            => 'localhost',
            'port'            => 587,
            'auth'            => true,
            'secure'          => 'tls',
            'username'        => '',
            'password'        => '',
            'smtp_debug'      => 4,
            'message_options' => [
                'CharSet'   => 'UTF-8',
                'isHtml'    => true,
                'Timeout'   => 15,
            ],
        ];

        // Set dependencies services
        $config = m::mock(Config::class);
        $config->shouldReceive('get')->with('mail')->andReturn($mailConfig);
        $config->shouldReceive('get')->with('debug.smtp')->andReturn(false); // TODO : Test true...
        $ci->set(Config::class, $config);

        // Set dependencies services
        $logger = m::mock(MailLogger::class);
        $ci->set(MailLogger::class, $logger);

        // Assertions
        $this->assertInstanceOf(Mailer::class, $ci->get(Mailer::class));
    }
}
