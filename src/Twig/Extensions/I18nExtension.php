<?php

declare(strict_types=1);

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;

class I18nExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param Translator $translator
     */
    public function __construct(
        protected Translator $translator,
        protected SiteLocale $locale,
    ) {
    }

    /**
     * Adds Twig functions `translate`.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('translate', [$this->translator, 'translate'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Adds Twig global variables `site` and `assets`.
     *
     * @return array[mixed]
     */
    public function getGlobals(): array
    {
        return [
            'currentLocale' => $this->locale->getLocaleIdentifier(),
        ];
    }
}
