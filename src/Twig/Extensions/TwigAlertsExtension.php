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
use UserFrosting\Alert\AlertStream;

class TwigAlertsExtension extends AbstractExtension
{
    /**
     * @param AlertStream $alertStream
     */
    public function __construct(protected AlertStream $alertStream)
    {
    }

    /**
     * Adds Twig functions `getAlerts`.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getAlerts', [$this, 'getAlerts']),
        ];
    }

    /**
     * Get the current alerts and optionally clear them.
     *
     * @param bool $clear Clear
     *
     * @return array Messages to display
     */
    public function getAlerts(bool $clear = true): array
    {
        if ($clear) {
            return $this->alertStream->getAndClearMessages();
        } else {
            return $this->alertStream->messages();
        }
    }
}
