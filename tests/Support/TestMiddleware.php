<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Tests\Support;

use Closure;

/**
 * A plain middleware for use in tests that records its execution in a shared log.
 */
final class TestMiddleware
{
    /**
     * Creates a new TestMiddleware instance.
     *
     * @param array<int, string> $log The shared log to record execution into.
     * @param string $label The label to append to the log on execution.
     */
    public function __construct(
        private array  &$log,
        private string $label = 'middleware',
    ) {
    }

    /**
     * Appends the label to the log and passes the command to the next middleware.
     *
     * @param object $command The command being dispatched.
     * @param Closure $next The next middleware or handler in the pipeline.
     * @return mixed The result of the next middleware or handler.
     */
    public function execute(object $command, Closure $next): mixed
    {
        $this->log[] = $this->label;

        return $next($command);
    }
}
