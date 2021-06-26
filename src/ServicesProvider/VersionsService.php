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
use UserFrosting\Sprinkle\Core\Validators\NodeVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\NpmVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpDeprecationValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpVersionValidator;

/*
 * Define
 */
class VersionsService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // Minimum requirements
            'PHP_MIN_VERSION'           => '^8.0',
            'PHP_RECOMMENDED_VERSION'   => '^8.0',
            'NODE_MIN_VERSION'          => '^12.17.0 || >=14.0.0',
            'NPM_MIN_VERSION'           => '>=6.14.4',

            // Installed version
            'PHP_VERSION'  => (string) phpversion(),
            'NODE_VERSION' => exec('node -v'), // TODO : Required Try catch
            'NPM_VERSION'  => exec('npm -v'),

            // Version validators
            PhpVersionValidator::class => function (ContainerInterface $c) {
                return new PhpVersionValidator($c->get('PHP_VERSION'), $c->get('PHP_MIN_VERSION'));
            },

            PhpDeprecationValidator::class => function (ContainerInterface $c) {
                return new PhpDeprecationValidator($c->get('PHP_VERSION'), $c->get('PHP_RECOMMENDED_VERSION'));
            },

            NodeVersionValidator::class => function (ContainerInterface $c) {
                return new NodeVersionValidator($c->get('NODE_VERSION'), $c->get('NODE_MIN_VERSION'));
            },

            NpmVersionValidator::class => function (ContainerInterface $c) {
                return new NpmVersionValidator($c->get('NPM_VERSION'), $c->get('NPM_MIN_VERSION'));
            },
        ];
    }
}
