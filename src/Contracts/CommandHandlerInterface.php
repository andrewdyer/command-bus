<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Contracts;

/**
 * Defines the contract for command handlers.
 */
interface CommandHandlerInterface
{
    /**
     * Handles the given command.
     *
     * @param CommandInterface $command The command to handle.
     * @return mixed The result of handling the command.
     */
    public function handle(CommandInterface $command): mixed;
}
