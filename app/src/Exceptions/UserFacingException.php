<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Exceptions;

use Exception;
use Throwable;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\TwigRenderedException;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\UserMessageException;
use UserFrosting\Support\Message\UserMessage;

/**
 * User facing exception shows a specific message and optional description meant
 * for to the user, unlike other exceptions which shows a generic message and
 * log detail for the developer. Theses can be used to show a specific error
 * message to the user, like `password not match` or `group not found`. They
 * are backed by a Twig template and a HTTP error code.
 *
 * This class is meant to be extended to create specific exceptions.
 */
class UserFacingException extends Exception implements TwigRenderedException, UserMessageException
{
    protected string $title = 'ERROR.TITLE';
    protected string|UserMessage $description = 'ERROR.DESCRIPTION';
    protected string $twigTemplate = 'pages/error/error.html.twig';
    protected int $httpCode = 400; // Default to 400 Bad Request

    /**
     * {@inheritDoc}
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $code = ($code === 0) ? $this->httpCode : $code;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplate(): string
    {
        return $this->twigTemplate;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle(): string|UserMessage
    {
        return $this->title;
    }

    /**
     * Set the value of title.
     *
     * @return static
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string|UserMessage
    {
        return $this->description;
    }

    /**
     * Set the value of description.
     *
     * @return static
     */
    public function setDescription(string|UserMessage $description): static
    {
        $this->description = $description;

        return $this;
    }
}
