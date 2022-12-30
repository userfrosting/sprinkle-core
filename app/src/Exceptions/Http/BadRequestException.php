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
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\TwigRenderedException;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\UserMessageException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Base exception for Bad Request related Exception. Used as base for many misc
 * user facing errors that include a twig template.
 */
class BadRequestException extends Exception implements TwigRenderedException, UserMessageException
{
    protected string $title = 'ERROR.400.TITLE';
    protected string|UserMessage $description = 'ERROR.400.TITLE';
    protected string $twigTemplate = 'pages/error/error.html.twig';
    protected int $httpCode = 400;

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
