<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database;

use UserFrosting\Support\Exception\ForbiddenException;

/**
 * Invalid database exception.  Used when the database cannot be accessed.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class DatabaseInvalidException extends ForbiddenException
{
    protected $defaultMessage = 'DB_INVALID';
}
