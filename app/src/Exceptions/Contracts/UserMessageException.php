<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Exceptions\Contracts;

interface UserMessageException
{
    /**
     * Return user facing message title.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Return user facing message description.
     *
     * @return string
     */
    public function getDescription(): string;
}
