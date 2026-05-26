<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Middleware;

use Psr\Log\LoggerInterface;

/**
 * Middleware that logs command dispatch and completion.
 */
readonly class LoggingMiddleware
{
    /**
     * Creates a new logging middleware instance.
     *
     * @param LoggerInterface $logger The logger instance.
     */
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Processes a command through the middleware pipeline.
     *
     * @param object $command The command to process.
     * @param \Closure $next The next middleware in the pipeline.
     * @return mixed The result of the middleware execution.
     */
    public function execute(object $command, \Closure $next): mixed
    {
        $commandClass = get_class($command);

        $this->logger->info("Dispatching command: {$commandClass}");

        $result = $next($command);

        $this->logger->info("Command handled: {$commandClass}");

        return $result;
    }
}
