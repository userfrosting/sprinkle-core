<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Psr\Container\ContainerInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Database\Seeder\Seeder;

/*
 * Return an instance of the database seeder
 */
class SeederService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // TODO Rework injection
            // TODO Add interface
            Seeder::class => function (ContainerInterface $c) {
                return new Seeder($c);
            },
        ];
    }
}
