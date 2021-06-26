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
            // TODO : Custom exception should be used.
            // TODO : Container could be used to instantiate *AlertStream and limit the number of dependencies required (but would depend on the whole container... PHP-DI docs should be consulted to find the best way to do this).
            AlertStream::class => function (Config $config, Translator $translator, Cache $cache, Session $session) {
                switch ($config->get('alert.storage')) {
                    case 'cache':
                        return new CacheAlertStream($config->get('alert.key'), $translator, $cache, $session->getId());
                    break;
                    case 'session':
                        return new SessionAlertStream($config->get('alert.key'), $translator, $session);
                    break;
                    default:
                        throw new \Exception("Bad alert storage handler type '{$config->get('alert.storage')}' specified in configuration file.");
                    break;
                }
            },
        ];
    }
}
