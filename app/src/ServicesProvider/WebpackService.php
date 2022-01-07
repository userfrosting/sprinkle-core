<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Lcharette\WebpackEncoreTwig\JsonManifest;
use Lcharette\WebpackEncoreTwig\JsonManifestInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Support\Repository\Repository as Config;

class WebpackService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            EntrypointLookupInterface::class => function () {
                return new EntrypointLookup('public://assets/entrypoints.json'); // TODO : Use Config
            },
            JsonManifestInterface::class => function () {
                return new JsonManifest('public://assets/manifest.json'); // TODO : Use Config
            },
        ];
    }
}
