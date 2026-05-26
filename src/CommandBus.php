<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus;

use AndrewDyer\CommandBus\Exceptions\HandlerNotFoundException;
use InvalidArgumentException;

/**
 * Dispatches commands to their registered handlers through a middleware pipeline.
 */
class CommandBus
{
    /**
     * Registered command handlers indexed by command class name.
     */
    private array $handlers = [];

    /**
     * Registered middleware to be applied to command dispatch.
     */
    private array $middleware = [];

    /**
     * Registers a handler for a specific command class.
     *
     * @param string $commandClass The fully qualified command class name.
     * @param object $handler The handler instance.
     * @return self The command bus instance for method chaining.
     * @throws InvalidArgumentException When the handler does not implement a handle() method.
     */
    public function register(string $commandClass, object $handler): self
    {
        if (!is_callable([$handler, 'handle'])) {
            throw new InvalidArgumentException(
                get_class($handler) . ' must implement a public handle() method.'
            );
        }

        $this->handlers[$commandClass] = $handler;

        return $this;
    }

    /**
     * Registers middleware to be applied during command dispatch.
     *
     * @param object $middleware The middleware instance.
     * @return self The command bus instance for method chaining.
     * @throws InvalidArgumentException When the middleware does not implement an execute() method.
     */
    public function addMiddleware(object $middleware): self
    {
        if (!is_callable([$middleware, 'execute'])) {
            throw new InvalidArgumentException(
                get_class($middleware) . ' must implement a public execute() method.'
            );
        }

        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Processes a command through the middleware pipeline and invokes its handler.
     *
     * @param object $command The command to dispatch.
     * @return mixed The result from the command handler.
     * @throws HandlerNotFoundException When no handler is registered for the command.
     */
    public function dispatch(object $command): mixed
    {
        $chain = array_reduce(
            array_reverse($this->middleware),
            fn (callable $next, object $middleware) => fn (object $cmd) => $middleware->execute($cmd, $next),
            fn (object $cmd) => $this->callHandler($cmd)
        );

        return $chain($command);
    }

    /**
     * Resolves and invokes the handler for the given command.
     *
     * @param object $command The command to handle.
     * @return mixed The result from the command handler.
     * @throws HandlerNotFoundException When no handler is registered for the command.
     *
     * @internal
     */
    private function callHandler(object $command): mixed
    {
        $class = get_class($command);

        if (!isset($this->handlers[$class])) {
            throw new HandlerNotFoundException("No handler registered for {$class}.");
        }

        return $this->handlers[$class]->handle($command);
    }
}
