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
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Translator services provider.
 *
 * Register via Autowire :
 *  - Translator::class
 */
class I18nService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO : Locale should be injected somehow... SiteLocale should probably extend Locale...
            DictionaryInterface::class => function (SiteLocale $siteLocale, ResourceLocatorInterface $locator) {
                $locale = new Locale($siteLocale->getLocaleIdentifier());

                return new Dictionary($locale, $locator);
            },
        ];
    }
}
