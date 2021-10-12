<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Throttle\ThrottleRule;
use UserFrosting\Support\Repository\Repository as Config;

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
