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
use UserFrosting\Support\Message\UserMessage;
use UserFrosting\Support\Repository\Repository as Config;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final class PrettyPageRenderer implements ErrorRendererInterface
{
    public function __construct(
        protected Config $config,
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

        // Show Whoops page if error details is active
        if ($displayErrorDetails) {
            return $this->whoops->handleException($exception);
        }

        // Render Twig pretty page otherwise
        return $this->renderTwigPage($userMessages, $statusCode);
    }

    /**
     * Render a generic, user-friendly response without sensitive debugging information.
     *
     * @param UserMessage[] $userMessages
     * @param int           $statusCode
     *
     * @return string
     */
    protected function renderTwigPage(array $userMessages, int $statusCode): string
    {
        $payload = [
            'messages' => $userMessages,
        ];

        try {
            $page = sprintf($this->config->get('error.pages.status'), $statusCode);
            $body = $this->twig->fetch($page, $payload);
        } catch (LoaderError $e) {
            $body = $this->twig->fetch($this->config->get('error.pages.error'), $payload);
        }

        return $body;
    }
}
