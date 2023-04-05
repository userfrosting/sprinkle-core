<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery\Helper;

use DI\Attribute\Inject;
use UserFrosting\I18n\Locale;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;

/**
 * Locale Helper.
 *
 * Provides:
 *  - askForLocale
 *  - getLocale
 */
trait LocaleOption
{
    #[Inject]
    protected SiteLocale $locale;

    /**
     * Display locale selection question.
     *
     * @return string Selected locale identifier
     */
    protected function askForLocale(string $name, bool $default = true): string
    {
        $availableLocales = $this->locale->getAvailableIdentifiers();

        if ($default) {
            $defaultLocale = $this->locale->getDefaultLocale();
        } else {
            $defaultLocale = null;
        }

        $answer = $this->io->choice("Select $name", $availableLocales, $defaultLocale);

        return $answer;
    }

    protected function getLocale(?string $option): Locale
    {
        $identifier = ($option) ?: $this->askForLocale('locale');
        if (!$this->locale->isAvailable($identifier)) {
            $this->io->error("Locale `$identifier` is not available");
            exit(1);
        }

        return new Locale($identifier);
    }
}
