<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
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
