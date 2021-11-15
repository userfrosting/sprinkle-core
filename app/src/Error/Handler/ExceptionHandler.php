<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;
use UserFrosting\Sprinkle\Core\Error\Renderer\ErrorRendererInterface;
use UserFrosting\Sprinkle\Core\Error\Renderer\HtmlRenderer;
use UserFrosting\Sprinkle\Core\Error\Renderer\JsonRenderer;
use UserFrosting\Sprinkle\Core\Error\Renderer\PlainTextRenderer;
use UserFrosting\Sprinkle\Core\Error\Renderer\PrettyPageRenderer;
use UserFrosting\Sprinkle\Core\Error\Renderer\XmlRenderer;
use UserFrosting\Sprinkle\Core\Http\Concerns\DeterminesContentType;
use UserFrosting\Support\Message\UserMessage;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Generic handler for exceptions.
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    use DeterminesContentType;

    /**
     * @var string[] Renderers for normal
     */
    protected $errorRenderers = [
        'application/json' => JsonRenderer::class,
        'application/xml'  => XmlRenderer::class,
        'text/xml'         => XmlRenderer::class,
        'text/html'        => HtmlRenderer::class,
        'text/plain'       => PlainTextRenderer::class,
    ];

    public function __construct(
        protected ContainerInterface $ci,
        protected ResponseFactory $responseFactory,
        protected Config $config,
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable              $exception
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        // TODO
        // if ($this->config->get('logs.exception')) {
        //     $this->writeToErrorLog();
        // }

        // TODO
        // If this is an AJAX request and AJAX debugging is turned off, write messages to the alert stream
        // if ($this->request->isXhr() && !$this->ci->config['site.debug.ajax']) {
        //     $this->writeAlerts();
        // }

        // Render Response
        $response = $this->renderResponse($request, $exception);

        return $response;
    }

    /**
     * Render a detailed response with debugging information.
     *
     * @return ResponseInterface
     */
    public function renderResponse(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $statusCode = $this->determineStatusCode($request, $exception);
        $contentType = $this->determineContentType($request);
        $response = $this->responseFactory->createResponse($statusCode);
        $messages = $this->determineUserMessages($exception);

        // Determine which renderer to use based on the content type and required details
        $renderer = $this->determineRenderer($contentType);

        // Write to the response body
        $body = $renderer->render($request, $exception, $messages, $statusCode, $this->displayErrorDetails());
        $response->getBody()->write($body);

        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-type', $contentType);
    }

    /**
     * @return bool
     */
    protected function displayErrorDetails(): bool
    {
        return $this->config->get('debug.exception');
    }

    /**
     * Write to the error log.
     */
    public function writeToErrorLog()
    {
        $renderer = new PlainTextRenderer($this->request, $this->response, $this->exception, true);
        $error = $renderer->render();
        $error .= PHP_EOL . 'View in rendered output by enabling the "debug.exception" setting.' . PHP_EOL;
        $this->logError($error);
    }

    /**
     * Write user-friendly error messages to the alert message stream.
     */
    public function writeAlerts()
    {
        $messages = $this->determineUserMessages();

        foreach ($messages as $message) {
            $this->ci->alerts->addMessageTranslated('danger', $message->message, $message->parameters);
        }
    }

    /**
     * Determine which renderer to use based on content type
     * Overloaded $renderer from calling class takes precedence over all.
     *
     * @throws \RuntimeException
     *
     * @return ErrorRendererInterface
     */
    protected function determineRenderer(string $contentType): ErrorRendererInterface
    {
        // TODO : Register those / use param / move to config
        switch ($contentType) {
            case 'application/json':
                $renderer = JsonRenderer::class;
                break;

            case 'text/xml':
            case 'application/xml':
                $renderer = XmlRenderer::class;
                break;

            case 'text/plain':
                $renderer = PlainTextRenderer::class;
                break;

            default:
            case 'text/html':
                $renderer = PrettyPageRenderer::class;
                // $renderer = HtmlRenderer::class;
                break;
        }

        // Make sure it's a valid interface before returning
        // TODO

        return $this->ci->get($renderer);
    }

    /**
     * Resolve the status code to return in the response from this handler.
     *
     * @return int
     */
    protected function determineStatusCode(ServerRequestInterface $request, Throwable $exception): int
    {
        if ($request->getMethod() === 'OPTIONS') {
            return 200;
        } elseif ($exception instanceof HttpException) {
            return $exception->getCode();
        }

        return 500;
    }

    /**
     * Resolve a list of error messages to present to the end user.
     *
     * @return UserMessage[]
     */
    protected function determineUserMessages(Throwable $exception): array
    {
        // TODO
        // if ($exception instanceof HttpException) {
        //     return $exception->getUserMessages();
        // }

        return [
            new UserMessage('ERROR.SERVER'),
        ];
    }

    /**
     * Monolog logging for errors.
     *
     * @param string $message
     */
    protected function logError(string $message): void
    {
        $this->ci->errorLogger->error($message); // TODO
    }
}
