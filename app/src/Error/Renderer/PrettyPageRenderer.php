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
use Slim\Views\Twig;
use Throwable;
use Twig\Error\LoaderError;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final class PrettyPageRenderer implements ErrorRendererInterface
{
    public function __construct(
        protected Twig $twig,
        protected Run $whoops,
        protected PrettyPageHandler $handler,
    ) {
        $this->whoops->appendHandler($handler);
    }

    /**
     * {@inheritDoc}
     */
    public function render(
        ServerRequestInterface $request,
        Throwable $exception,
        array $userMessages,
        int $statusCode,
        bool $displayErrorDetails = false
    ): string {
        if ($displayErrorDetails) {
            return $this->whoops->handleException($exception);
        }

        return $this->renderTwigPage($userMessages, $statusCode);
    }

    /**
     * Render a generic, user-friendly response without sensitive debugging information.
     *
     * @param array $userMessages
     * @param int   $statusCode
     *
     * @return string
     */
    protected function renderTwigPage(array $userMessages, int $statusCode): string
    {
        $payload = [
            'messages' => $userMessages,
        ];

        // TODO : Move string to config
        try {
            $body = $this->twig->fetch("pages/error/$statusCode.html.twig", $payload);
        } catch (LoaderError $e) {
            $body = $this->twig->fetch('pages/abstract/error.html.twig', $payload);
        }

        return $body;
    }
}
