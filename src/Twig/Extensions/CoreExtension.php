<?php

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
use Twig\TwigFilter;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Assets\Assets;
use UserFrosting\Assets\AssetsTemplatePlugin;
use UserFrosting\Sprinkle\Core\I18n\SiteLocale;
use UserFrosting\Sprinkle\Core\Util\Util;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Extends Twig functionality for the Core sprinkle.
 */
// TODO : This should be separated in multiple Extension and registered in CoreRecipe
class CoreExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param AlertStream $translator The alert stream service
     * @param Assets      $assets     The assets service
     * @param Config      $config     The config service
     * @param SiteLocale  $locale     The site locale service
     */
    public function __construct(
        protected Assets $assets,
        protected Config $config,
        protected SiteLocale $locale,
    ) {
    }

    /**
     * Adds Twig filters `unescape`.
     *
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            /*
             * Converts phone numbers to a standard format.
             *
             * @param   string   $num   A unformatted phone number
             * @return  string   Returns the formatted phone number
             */
            new TwigFilter('phone', function ($num) {
                return Util::formatPhoneNumber($num);
            }),
            new TwigFilter('unescape', function ($string) {
                return html_entity_decode($string);
            }),
        ];
    }

    /**
     * Adds Twig global variables `site` and `assets`.
     *
     * @return array[mixed]
     */
    public function getGlobals(): array
    {
        // CSRF token name and value
        // TODO : Needs new CSRF service
        /*$csrfNameKey = $this->services->csrf->getTokenNameKey();
        $csrfValueKey = $this->services->csrf->getTokenValueKey();
        $csrfName = $this->services->csrf->getTokenName();
        $csrfValue = $this->services->csrf->getTokenValue();

        $csrf = [
            'csrf'   => [
                'keys' => [
                    'name'  => $csrfNameKey,
                    'value' => $csrfValueKey,
                ],
                'name'  => $csrfName,
                'value' => $csrfValue,
            ],
        ];

        $site = array_replace_recursive($this->services->config['site'], $csrf);
        */
        //TEMP :
        $site = $this->config->get('site');

        return [
            'site'          => $site,
            'assets'        => new AssetsTemplatePlugin($this->assets),
            'currentLocale' => $this->locale->getLocaleIdentifier(),
        ];
    }
}
