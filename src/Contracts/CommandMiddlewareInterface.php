<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Contracts;

interface CommandMiddlewareInterface
{
    public function execute(CommandInterface $command, callable $next): mixed;
}
