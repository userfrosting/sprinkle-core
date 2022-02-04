<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Throttle;

use Exception;
use Throwable;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\TwigRenderedException;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\UserMessageException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Exception thrown when the throttler delay
 */
final class ThrottlerDelayException extends Exception implements TwigRenderedException, UserMessageException
{
    protected string $title = 'ERROR.RATE_LIMIT_EXCEEDED.TITLE';
    protected string $description = 'ERROR.RATE_LIMIT_EXCEEDED.DESCRIPTION';
    protected string $twigTemplate = 'pages/error/throttler.html.twig';

    /**
     * @var int $delay The delay before the user can re-attempt
     */
    protected int $delay = 0;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $message = '', int $code = 429, ?Throwable $previous = null)
    {
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
     * {@inheritDoc}
     */
    public function getDescription(): string|UserMessage
    {
        return new UserMessage($this->description, ['delay' => $this->delay]);
    }

    /**
     * Set delay before the user can re-attempt.
     *
     * @param int $delay
     */
    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }
}
