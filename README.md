# Command Bus

A lightweight command bus with middleware pipeline support for PHP 8.3+.

## Introduction

This library provides a command bus implementation for PHP applications, enabling commands to be dispatched to their registered handlers through a configurable middleware pipeline. It supports handler registration, middleware chaining, and ships with a PSR-3 compatible logging middleware out of the box.

## Prerequisites

- **[PHP](https://www.php.net/)**: Version 8.3 or higher is required.
- **[Composer](https://getcomposer.org/)**: Dependency management tool for PHP.

## Installation

```bash
composer require andrewdyer/command-bus
```

## Getting Started

### 1. Create a command

Commands are plain objects that implement `CommandInterface`. They carry the data required to perform an operation:

```php
use AndrewDyer\CommandBus\Contracts\CommandInterface;

class CreateUserCommand implements CommandInterface
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
    ) {}
}
```

### 2. Create a handler

Handlers implement `CommandHandlerInterface` and contain the logic for processing a command:

```php
use AndrewDyer\CommandBus\Contracts\CommandHandlerInterface;
use AndrewDyer\CommandBus\Contracts\CommandInterface;

class CreateUserHandler implements CommandHandlerInterface
{
    public function handle(CommandInterface $command): mixed
    {
        // Handle the command...

        return $user;
    }
}
```

### 3. Create the command bus and register the handler

```php
use AndrewDyer\CommandBus\CommandBus;

$bus = new CommandBus();

$bus->register(CreateUserCommand::class, new CreateUserHandler());
```

## Usage

Once the command bus is configured, commands can be dispatched to their registered handlers. If no handler is registered for a given command, a `HandlerNotFoundException` is thrown.

### Dispatching a command

```php
$user = $bus->dispatch(new CreateUserCommand(
    firstName: 'Oliver',
    lastName: 'French',
    email: 'oliver.french@example.com',
));
```

### Adding middleware

Middleware intercepts commands before they reach the handler, allowing cross-cutting concerns such as logging, transactions, or validation to be applied consistently across all commands.

#### Logging middleware

A `LoggingMiddleware` is included out of the box. It accepts any PSR-3 compatible logger and logs the command class name before and after dispatch:

```php
use AndrewDyer\CommandBus\Middleware\LoggingMiddleware;

$bus->addMiddleware(new LoggingMiddleware($logger));
```

#### Custom middleware

Custom middleware implements `CommandMiddlewareInterface` and receives the command and a `$next` callable to pass control down the pipeline:

```php
use AndrewDyer\CommandBus\Contracts\CommandInterface;
use AndrewDyer\CommandBus\Contracts\CommandMiddlewareInterface;

class TransactionMiddleware implements CommandMiddlewareInterface
{
    public function execute(CommandInterface $command, callable $next): mixed
    {
        // Begin transaction...

        try {
            $result = $next($command);
        } catch (\Throwable $e) {
            // Rollback transaction...

            throw $e;
        }

        // Commit transaction...

        return $result;
    }
}
```

Middleware is executed in the order it is registered, so the first middleware added is the first to intercept the command:

```php
$bus->addMiddleware(new TransactionMiddleware());
$bus->addMiddleware(new LoggingMiddleware($logger));
```

## License

Licensed under the [MIT license](https://opensource.org/licenses/MIT) and is free for private or commercial projects.
