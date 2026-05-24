<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Middleware;

use AndrewDyer\CommandBus\Contracts\CommandInterface;
use AndrewDyer\CommandBus\Contracts\CommandMiddlewareInterface;
use Psr\Log\LoggerInterface;

readonly class LoggingMiddleware implements CommandMiddlewareInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function execute(CommandInterface $command, callable $next): mixed
    {
        $commandClass = get_class($command);

        $this->logger->info("Dispatching command: {$commandClass}");

        $result = $next($command);

        $this->logger->info("Command handled: {$commandClass}");

        return $result;
    }
}
