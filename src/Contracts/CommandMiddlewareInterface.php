<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Contracts;

/**
 * Defines the contract for command middleware.
 */
interface CommandMiddlewareInterface
{
    /**
     * Processes a command through the middleware pipeline.
     *
     * @param CommandInterface $command The command to process.
     * @param callable $next The next middleware in the pipeline.
     * @return mixed The result of the middleware execution.
     */
    public function execute(CommandInterface $command, callable $next): mixed;
}
