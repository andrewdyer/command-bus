![Command Bus](http://public-assets.andrewdyer.rocks/images/covers/command-bus.png)

<p align="center">
  <a href="https://packagist.org/packages/andrewdyer/command-bus"><img src="https://poser.pugx.org/andrewdyer/command-bus/v/stable?style=for-the-badge" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/andrewdyer/command-bus"><img src="https://poser.pugx.org/andrewdyer/command-bus/downloads?style=for-the-badge" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/andrewdyer/command-bus"><img src="https://poser.pugx.org/andrewdyer/command-bus/license?style=for-the-badge" alt="License"></a>
  <a href="https://packagist.org/packages/andrewdyer/command-bus"><img src="https://poser.pugx.org/andrewdyer/command-bus/require/php?style=for-the-badge" alt="PHP Version Required"></a>
</p>

# Command Bus

A framework-agnostic PHP library for dispatching commands to handlers, with support for a configurable middleware pipeline.

## Introduction

This library provides a framework-agnostic command bus for PHP applications, dispatching commands to their registered handlers through a configurable middleware pipeline, with a PSR-3 compatible logging middleware included out of the box.

## Prerequisites

- **[PHP](https://www.php.net/)**: Version 8.3 or higher is required.
- **[Composer](https://getcomposer.org/)**: Dependency management tool for PHP.

## Installation

```bash
composer require andrewdyer/command-bus
```

## Getting Started

### 1. Create a command

Create a command by defining a plain object that implements `CommandInterface`, carrying the data required to perform an operation:

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

Create a handler as a plain class with a `handle` method typed to accept the specific command it processes:

```php
class CreateUserHandler
{
    public function handle(CreateUserCommand $command): mixed
    {
        // Handle the command...

        return $user;
    }
}
```

### 3. Create the command bus and register the handler

Create a `CommandBus` instance and register the handler against the command class it should handle:

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
    firstName: 'John',
    lastName: 'Doe',
    email: 'john.doe@example.com',
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
