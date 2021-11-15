<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * ErrorRendererInterface.
 */
interface ErrorRendererInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $exception
     * @param UserMessage[]          $userMessages
     * @param int                    $statusCode
     * @param bool                   $displayErrorDetails
     *
     * @return string
     */
    public function render(
        ServerRequestInterface $request,
        Throwable $exception,
        array $userMessages,
        int $statusCode,
        bool $displayErrorDetails = false
    ): string;
}
