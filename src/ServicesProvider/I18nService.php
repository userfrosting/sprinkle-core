<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use UserFrosting\I18n\Dictionary;
use UserFrosting\I18n\Locale;
use UserFrosting\I18n\Translator;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Translator services provider.
 *
 * Registers:
 *  - translator : \UserFrosting\I18n\Translator
 */
class I18nService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO : Locale and Dictionary should be injected somehow...
            //        Dictionary should be easier to get in it's own service.
            Translator::class => function (SiteLocale $siteLocale, ResourceLocatorInterface $locator) {
                $locale = new Locale($siteLocale->getLocaleIdentifier());
                $dictionary = new Dictionary($locale, $locator);
                $translator = new Translator($dictionary);

                return $translator;
            },
        ];
    }
}
