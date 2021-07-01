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
use Twig\TwigFunction;
use UserFrosting\I18n\Translator;

class TwigI18nExtension extends AbstractExtension
{
    /**
     * @param Translator $translator
     */
    public function __construct(protected Translator $translator)
    {
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
}
