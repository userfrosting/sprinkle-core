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
use UserFrosting\Sprinkle\Core\Util\Util;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Test Twig functionality from CoreExtension.
 */
class CoreExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param Config $config The config service
     */
    public function __construct(
        protected Config $config,
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
        return [
            'site' => $this->config->get('site'),
        ];
    }
}
