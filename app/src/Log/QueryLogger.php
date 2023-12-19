<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Monolog alias for dependency injection.
 */
final class QueryLogger extends AbstractLogger implements QueryLoggerInterface
{
    protected LoggerInterface $logger;

    public function __construct(
        StreamHandler $handler,
        LineFormatter $formatter,
    ) {
        $formatter->setJsonPrettyPrint(true);
        $handler->setFormatter($formatter);

        $this->logger = new Logger('query');
        $this->logger->pushHandler($handler);
    }

    /**
     * @param mixed              $level
     * @param string|\Stringable $message
     * @param mixed[]            $context
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
