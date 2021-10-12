<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Psr\Container\ContainerInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Csrf\SlimCsrfProvider;

/*
 * Initialize CSRF guard middleware.
 *
 * @see https://github.com/slimphp/Slim-Csrf
 * @throws \UserFrosting\Support\Exception\BadRequestException
 * @return \Slim\Csrf\Guard
 */
class CsrfService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO Reimplement registerMiddleware and rework dependency injection
            'csrf' => function (ContainerInterface $c) {
                return SlimCsrfProvider::setupService($c);
            },
        ];
    }
}
