<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus;

use AndrewDyer\CommandBus\Contracts\CommandHandlerInterface;
use AndrewDyer\CommandBus\Contracts\CommandInterface;
use AndrewDyer\CommandBus\Contracts\CommandMiddlewareInterface;
use AndrewDyer\CommandBus\Exceptions\HandlerNotFoundException;

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
     * @param CommandHandlerInterface $handler The handler instance.
     * @return self The command bus instance for method chaining.
     */
    public function register(string $commandClass, CommandHandlerInterface $handler): self
    {
        $this->handlers[$commandClass] = $handler;

        return $this;
    }

    /**
     * Registers middleware to be applied during command dispatch.
     *
     * @param CommandMiddlewareInterface $middleware The middleware instance.
     * @return self The command bus instance for method chaining.
     */
    public function addMiddleware(CommandMiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Processes a command through the middleware pipeline and invokes its handler.
     *
     * @param CommandInterface $command The command to dispatch.
     * @return mixed The result from the command handler.
     * @throws HandlerNotFoundException When no handler is registered for the command.
     */
    public function dispatch(CommandInterface $command): mixed
    {
        $chain = array_reduce(
            array_reverse($this->middleware),
            fn (callable $next, CommandMiddlewareInterface $middleware) => fn (CommandInterface $cmd) => $middleware->execute($cmd, $next),
            fn (CommandInterface $cmd) => $this->callHandler($cmd)
        );

        return $chain($command);
    }

    /**
     * Resolves and invokes the handler for the given command.
     *
     * @param CommandInterface $command The command to handle.
     * @return mixed The result from the command handler.
     * @throws HandlerNotFoundException When no handler is registered for the command.
     *
     * @internal
     */
    private function callHandler(CommandInterface $command): mixed
    {
        $class = get_class($command);

        if (!isset($this->handlers[$class])) {
            throw new HandlerNotFoundException("No handler registered for {$class}.");
        }

        return $this->handlers[$class]->handle($command);
    }
}
