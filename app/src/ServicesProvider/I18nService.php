<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use UserFrosting\I18n\Dictionary;
use UserFrosting\I18n\DictionaryInterface;
use UserFrosting\I18n\Locale;
use UserFrosting\I18n\LocaleInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Translator services provider.
 *
 * Register via Autowire :
 *  - Translator::class
 *  - SiteLocale::class
 */
class I18nService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            DictionaryInterface::class => function (LocaleInterface $locale, ResourceLocatorInterface $locator) {
                return new Dictionary($locale, $locator);
            },

            LocaleInterface::class => function (SiteLocale $siteLocale) {
                return new Locale($siteLocale->getLocaleIdentifier());
            },
        ];
    }
}
