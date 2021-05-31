<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Throttle\ThrottleRule;

/*
* Request throttler.
*
* Throttles (rate-limits) requests of a predefined type, with rules defined in site config.
*
* @return \UserFrosting\Sprinkle\Core\Throttle\Throttler
*/
class ThrottlerService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO Replace ClassMapper
            // TODO Add interface
            Throttler::class => function (Config $config) {
                $throttler = new Throttler($c->classMapper);

                if ($config->has('throttles') && ($config['throttles'] !== null)) {
                    foreach ($config['throttles'] as $type => $rule) {
                        if ($rule) {
                            $throttleRule = new ThrottleRule($rule['method'], $rule['interval'], $rule['delays']);
                            $throttler->addThrottleRule($type, $throttleRule);
                        } else {
                            $throttler->addThrottleRule($type, null);
                        }
                    }
                }

                return $throttler;
            },
        ];
    }
}
