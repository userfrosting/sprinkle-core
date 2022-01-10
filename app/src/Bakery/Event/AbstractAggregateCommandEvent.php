<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery\Event;

/**
 * Base event used to aggregate sub-command in an umbrella command.
 *
 * The list of subcommands that will be run can be manipulated using this event
 * to insert custom subcommands into the callstack.
 */
abstract class AbstractAggregateCommandEvent
{
    /**
     * @param string[] $commands
     */
    public function __construct(protected array $commands)
    {
    }

    /**
     * @return string[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @param string[] $commands
     */
    public function setCommands(array $commands): void
    {
        $this->commands = $commands;
    }

    /**
     * @param string $command
     */
    public function addCommand(string $command): void
    {
        $this->commands[] = $command;
    }

    /**
     * @param string $command
     */
    public function prependCommand(string $command): void
    {
        array_unshift($this->commands, $command);
    }
}
