<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Log\MailLogger;
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
            Mailer::class => function (Config $config, MailLogger $logger) {
                $mailer = new Mailer($logger, $config->get('mail'));

                return $mailer;
            },
        ];
    }
}
