<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Exceptions\Http;

use Exception;
use Throwable;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\UserMessageException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Not found exception.
 */
class NotFoundException extends Exception implements UserMessageException
{
    protected string $title = 'ERROR.404.TITLE';
    protected string $description = 'ERROR.404.DESCRIPTION';

    /**
     * {@inheritDoc}
     */
    public function __construct(string $message = '', int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

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
}
