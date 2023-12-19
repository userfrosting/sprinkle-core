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
use Monolog\Level;

/**
 * Error Monolog wrapper.
 */
final class ErrorLogger extends Logger implements ErrorLoggerInterface
{
    public function __construct(
        StreamHandler $handler,
        LineFormatter $formatter,
    ) {
        $handler->setFormatter($formatter);
        $handler->setLevel(Level::Warning);

        parent::__construct($handler, 'errors');
    }
}
