<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Illuminate\Cache\Repository as Cache;
use Psr\Container\ContainerInterface;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Alert\CacheAlertStream;
use UserFrosting\Alert\SessionAlertStream;
use UserFrosting\I18n\Translator;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Exceptions\BadConfigException;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Flash messaging service.
 *
 * Persists error/success messages between requests in the session.
 *
 * @throws BadConfigException If alert handler is not supported
 */
class AlertStreamService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            /**
             * Select AlertStream based on Config.
             *
             * @throws BadConfigException
             */
            AlertStream::class => function (ContainerInterface $ci, Config $config) {
                return match ($config->get('alert.storage')) {
                    'cache'   => $ci->get(CacheAlertStream::class),
                    'session' => $ci->get(SessionAlertStream::class),
                    default   => throw new BadConfigException("Bad alert storage handler type '{$config->get('alert.storage')}' specified in configuration file."),
                };
            },

            // TODO : If config service is passed as argument, no need for this. A `setKey` on the interface would help.
            CacheAlertStream::class => function (Config $config, Translator $translator, Cache $cache, Session $session) {
                return new CacheAlertStream($config->get('alert.key'), $translator, $cache, $session->getId());
            },

            // TODO : If config service is passed as argument, no need for this. A `setKey` on the interface would help.
            SessionAlertStream::class => function (Config $config, Translator $translator, Session $session) {
                return new SessionAlertStream($config->get('alert.key'), $translator, $session);
            },
        ];
    }
}
