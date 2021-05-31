<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Psr\Container\ContainerInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;

/**
 * Locale service provider.
 *
 * Registers:
 *  - locale : \UserFrosting\Sprinkle\Core\I18n\SiteLocale
 */
class LocaleService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO : Add interface & rework injection
            SiteLocale::class => function (ContainerInterface $ci) {
                return new SiteLocale($ci);
            },
        ];
    }
}
