<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error;

use UserFrosting\Sprinkle\Core\Http\Concerns\DeterminesContentType;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Registers a handler to be invoked whenever the application shuts down.
 * If it shut down due to a fatal error, will generate a clean error message.
 *
 * @see https://www.slimframework.com/docs/v4/objects/application.html#advanced-notices-and-warnings-handling
 */
class ShutdownHandler
{
    use DeterminesContentType;

    /**
     * @var string[] Error types this handler handle.
     */
    protected array $errorTypes = [
        E_ERROR             => 'Fatal error',
        E_PARSE             => 'Parse error',
        E_CORE_ERROR        => 'PHP core error',
        E_COMPILE_ERROR     => 'Zend compile error',
        E_RECOVERABLE_ERROR => 'Catchable fatal error',
    ];

    public function __construct(
        protected Config $config,
    ) {
    }

    /**
     * Register this class with the shutdown handler.
     */
    public function register(): void
    {
        register_shutdown_function([$this, 'fatalHandler']);
    }

    /**
     * Set up the fatal error handler, so that we get a clean error message and alert instead of a WSOD.
     */
    public function fatalHandler(): void
    {
        $error = error_get_last();

        // Handle fatal errors and parse errors
        if ($error !== null && in_array($error['type'], array_keys($this->errorTypes), true)) {

            // Determine if error display is enabled in the shutdown handler.
            $displayErrors = (bool) $this->config->get('debug.exception');

            // Build the appropriate error message (debug or client)
            if ($displayErrors) {
                $errorMessage = $this->buildErrorInfoMessage($error);
            } else {
                $errorMessage = "Oops, looks like our server might have goofed.  If you're an admin, please ensure that <code>php.log_errors</code> is enabled, and then check the <strong>PHP</strong> error log.";
            }

            // For CLI, just print the message and exit.
            if (php_sapi_name() === 'cli') {
                exit($errorMessage . PHP_EOL);
            }

            // For all other environments, print a debug response for the requested data type
            echo $this->buildErrorPage($errorMessage);

            // If this is an AJAX request and AJAX debugging is turned off, write message to the alert stream
            // if ($this->ci->request->isXhr() && !$this->ci->config['site.debug.ajax']) {
            //     if ($this->ci->alerts && is_object($this->ci->alerts)) {
            //         $this->ci->alerts->addMessageTranslated('danger', $errorMessage);
            //     }
            // }

            header('HTTP/1.1 500 Internal Server Error');
            exit();
        }
    }

    /**
     * Build the error message string.
     *
     * @param (string|int)[] $error
     *
     * @return string
     */
    protected function buildErrorInfoMessage(array $error): string
    {
        $file = $error['file'];
        $line = (string) $error['line'];
        $message = $error['message'];
        $type = $this->errorTypes[$error['type']];

        return "<strong>$type</strong>: $message in <strong>$file</strong> on line <strong>$line</strong>";
    }

    /**
     * Build an error response of the appropriate type as determined by the request's Accept header.
     *
     * @param string $message
     *
     * @return string
     */
    protected function buildErrorPage(string $message): string
    {
        return $this->buildHtmlErrorPage($message);

        // TODO : Request is not in CI anymore...
        /*$contentType = $this->determineContentType($this->ci->request);

        switch ($contentType) {
            case 'application/json':
                $error = ['message' => $message];

                return json_encode($error, JSON_PRETTY_PRINT);

            case 'text/html':
                return $this->buildHtmlErrorPage($message);

            default:
            case 'text/plain':
                return $message;
        }*/
    }

    /**
     * Build an HTML error page from an error string.
     *
     * @param string $message
     *
     * @return string
     */
    protected function buildHtmlErrorPage(string $message): string
    {
        $title = 'UserFrosting Application Error';
        $html = "<p>$message</p>";

        return sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            '<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,' .
            'sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}' .
            '</style></head><body><h1>%s</h1>%s</body></html>',
            $title,
            $title,
            $html
        );
    }
}
