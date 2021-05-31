<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

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
            // TODO Inject mailLogger
            Mailer::class => function (Config $config) {
                $mailer = new Mailer($c->mailLogger, $config['mail']);

                // Use UF debug settings to override any service-specific log settings.
                if (!$config['debug.smtp']) {
                    $mailer->getPhpMailer()->SMTPDebug = 0;
                }

                return $mailer;
            },
        ];
    }
}
