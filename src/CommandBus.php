<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus;

use AndrewDyer\CommandBus\Contracts\CommandHandlerInterface;
use AndrewDyer\CommandBus\Contracts\CommandInterface;
use AndrewDyer\CommandBus\Contracts\CommandMiddlewareInterface;
use AndrewDyer\CommandBus\Exceptions\HandlerNotFoundException;

class CommandBus
{
    private array $handlers = [];

    private array $middleware = [];

    public function register(string $commandClass, CommandHandlerInterface $handler): self
    {
        $this->handlers[$commandClass] = $handler;

        return $this;
    }

    public function addMiddleware(CommandMiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    public function dispatch(CommandInterface $command): mixed
    {
        $chain = array_reduce(
            array_reverse($this->middleware),
            fn (callable $next, CommandMiddlewareInterface $middleware) => fn (CommandInterface $cmd) => $middleware->execute($cmd, $next),
            fn (CommandInterface $cmd) => $this->callHandler($cmd)
        );

        return $chain($command);
    }

    private function callHandler(CommandInterface $command): mixed
    {
        $class = get_class($command);

        if (!isset($this->handlers[$class])) {
            throw new HandlerNotFoundException("No handler registered for {$class}.");
        }

        return $this->handlers[$class]->handle($command);
    }
}
