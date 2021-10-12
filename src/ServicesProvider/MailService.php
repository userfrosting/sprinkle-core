<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use DI\Container;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Support\Repository\Repository as Config;

/*
 * Mail service.
 */
class MailService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO Inject mailLogger properly once it has it's own class
            //      Config should probably be injected too
            Mailer::class => function (Config $config, Container $c) {
                $mailer = new Mailer($c->get('mailLogger'), $config->get('mail'));

                // Use UF debug settings to override any service-specific log settings.
                // TODO : Should probably be done in Mailer ?
                if (!$config->get('debug.smtp')) {
                    $mailer->getPhpMailer()->SMTPDebug = 0;
                }

                return $mailer;
            },
        ];
    }
}
