<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Illuminate\Cache\Repository as Cache;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Alert\CacheAlertStream;
use UserFrosting\Alert\SessionAlertStream;
use UserFrosting\I18n\Translator;
use UserFrosting\Session\Session;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Support\Repository\Repository as Config;

/*
 * Flash messaging service.
 *
 * Persists error/success messages between requests in the session.
 *
 * @throws \Exception                                    If alert storage handler is not supported
 * @return \UserFrosting\Alert\AlertStream
*/
class AlertStreamService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            AlertStream::class => function (Config $config, Translator $translator, Cache $cache, Session $session) {
                if ($config['alert.storage'] == 'cache') {
                    return new CacheAlertStream($config['alert.key'], $translator, $cache, $session->getId());
                } elseif ($config['alert.storage'] == 'session') {
                    return new SessionAlertStream($config['alert.key'], $translator, $session);
                } else {
                    throw new \Exception("Bad alert storage handler type '{$config['alert.storage']}' specified in configuration file.");
                }
            },
        ];
    }
}
