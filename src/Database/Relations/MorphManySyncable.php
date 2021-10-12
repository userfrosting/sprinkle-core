<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Relations;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use UserFrosting\Sprinkle\Core\Database\Relations\Concerns\Syncable;

/**
 * A MorphMany relationship that constrains on the value of an additional foreign key in the pivot table.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 *
 * @see https://github.com/laravel/framework/blob/5.8/src/Illuminate/Database/Eloquent/Relations/MorphMany.php
 */
class MorphManySyncable extends MorphMany
{
    use Syncable;
}
