<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Contracts;

interface CommandHandlerInterface
{
    public function handle(CommandInterface $command): mixed;
}
