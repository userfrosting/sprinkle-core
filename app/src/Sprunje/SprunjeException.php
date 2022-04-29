<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Sprunje;

use Exception;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\UserMessageException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Sprunje related exception.
 */
class SprunjeException extends Exception implements UserMessageException
{
    protected string|UserMessage $title = 'VALIDATE.SPRUNJE';
    protected string|UserMessage $description;

    /**
     * {@inheritDoc}
     */
    public function getTitle(): string|UserMessage
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string|UserMessage
    {
        return $this->description;
    }

    /**
     * @param string|UserMessage $description
     *
     * @return static
     */
    public function setDescription(string|UserMessage $description): static
    {
        $this->description = $description;

        return $this;
    }
}
