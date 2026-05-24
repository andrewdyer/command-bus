<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Exceptions;

use RuntimeException;

/**
 * Exception thrown when no handler is registered for a command.
 */
class HandlerNotFoundException extends RuntimeException
{
}
