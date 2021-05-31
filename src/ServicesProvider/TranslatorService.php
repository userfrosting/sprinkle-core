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
class TranslatorService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            Translator::class => function (SiteLocale $locale, ResourceLocatorInterface $locator) {
                // Create the $translator object
                $locale = new Locale($locale->getLocaleIndentifier());
                $dictionary = new Dictionary($locale, $locator);
                $translator = new Translator($dictionary);

                return $translator;
            },
        ];
    }
}
