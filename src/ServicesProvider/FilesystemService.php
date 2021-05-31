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
use UserFrosting\Sprinkle\Core\Filesystem\FilesystemManager;
use UserFrosting\Support\Repository\Repository as Config;

/*
 * Filesystem Service
 */
class FilesystemService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            FilesystemManager::class => function (Config $config) {
                return new FilesystemManager($config);
            },
        ];
    }
}
