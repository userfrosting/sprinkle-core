<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Handler;

/**
 * Force HttpNotFoundException to not display error details.
 */
final class HttpNotFoundExceptionHandler extends ExceptionHandler
{
    /**
     * @return bool
     */
    protected function displayErrorDetails(): bool
    {
        return false;
    }
}
