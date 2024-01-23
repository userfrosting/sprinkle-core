<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Relations;

use Illuminate\Database\Eloquent\Relations\HasMany;
use UserFrosting\Sprinkle\Core\Database\Relations\Concerns\Syncable;

/**
 * A HasMany relationship that supports a `sync` method.
 *
 * @see https://github.com/laravel/framework/blob/5.8/src/Illuminate/Database/Eloquent/Relations/HasMany.php
 */
class HasManySyncable extends HasMany
{
    use Syncable;
}
