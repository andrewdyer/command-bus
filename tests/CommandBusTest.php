<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Tests;

use AndrewDyer\CommandBus\CommandBus;
use AndrewDyer\CommandBus\Contracts\CommandHandlerInterface;
use AndrewDyer\CommandBus\Contracts\CommandInterface;
use AndrewDyer\CommandBus\Contracts\CommandMiddlewareInterface;
use AndrewDyer\CommandBus\Exceptions\HandlerNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CommandBus.
 */
final class CommandBusTest extends TestCase
{
    /**
     * The command bus instance under test.
     */
    private CommandBus $bus;

    /**
     * Sets up the test fixture before each test method.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = new CommandBus();
    }

    /**
     * Asserts that the command bus dispatches a command to its registered handler.
     */
    public function testDispatchesCommandToRegisteredHandler(): void
    {
        $command = new class () implements CommandInterface {
        };

        $handler = new class () implements CommandHandlerInterface {
            public bool $called = false;

            public function handle(CommandInterface $command): mixed
            {
                $this->called = true;

                return null;
            }
        };

        $this->bus->register(get_class($command), $handler);
        $this->bus->dispatch($command);

        $this->assertTrue($handler->called);
    }

    /**
     * Asserts that the dispatch method returns the result from the handler.
     */
    public function testDispatchReturnsHandlerResult(): void
    {
        $command = new class () implements CommandInterface {
        };

        $handler = new class () implements CommandHandlerInterface {
            public function handle(CommandInterface $command): mixed
            {
                return 'expected result';
            }
        };

        $this->bus->register(get_class($command), $handler);
        $result = $this->bus->dispatch($command);

        $this->assertSame('expected result', $result);
    }

    /**
     * Asserts that dispatching an unregistered command throws HandlerNotFoundException.
     */
    public function testThrowsHandlerNotFoundExceptionForUnregisteredCommand(): void
    {
        $command = new class () implements CommandInterface {
        };

        $this->expectException(HandlerNotFoundException::class);

        $this->bus->dispatch($command);
    }

    /**
     * Asserts that middleware is executed before the handler.
     */
    public function testMiddlewareIsExecutedBeforeHandler(): void
    {
        $log = [];

        $command = new class () implements CommandInterface {
        };

        $handler = new class ($log) implements CommandHandlerInterface {
            public function __construct(private array &$log)
            {
            }

            public function handle(CommandInterface $command): mixed
            {
                $this->log[] = 'handler';

                return null;
            }
        };

        $middleware = new class ($log) implements CommandMiddlewareInterface {
            public function __construct(private array &$log)
            {
            }

            public function execute(CommandInterface $command, callable $next): mixed
            {
                $this->log[] = 'middleware';

                return $next($command);
            }
        };

        $this->bus->register(get_class($command), $handler);
        $this->bus->addMiddleware($middleware);
        $this->bus->dispatch($command);

        $this->assertSame(['middleware', 'handler'], $log);
    }

    /**
     * Asserts that multiple middleware execute in the order they were added.
     */
    public function testMiddlewareExecutesInOrderAdded(): void
    {
        $log = [];

        $command = new class () implements CommandInterface {
        };

        $handler = new class ($log) implements CommandHandlerInterface {
            public function __construct(private array &$log)
            {
            }

            public function handle(CommandInterface $command): mixed
            {
                $this->log[] = 'handler';

                return null;
            }
        };

        $first = new class ($log) implements CommandMiddlewareInterface {
            public function __construct(private array &$log)
            {
            }

            public function execute(CommandInterface $command, callable $next): mixed
            {
                $this->log[] = 'first';

                return $next($command);
            }
        };

        $second = new class ($log) implements CommandMiddlewareInterface {
            public function __construct(private array &$log)
            {
            }

            public function execute(CommandInterface $command, callable $next): mixed
            {
                $this->log[] = 'second';

                return $next($command);
            }
        };

        $this->bus->register(get_class($command), $handler);
        $this->bus->addMiddleware($first);
        $this->bus->addMiddleware($second);
        $this->bus->dispatch($command);

        $this->assertSame(['first', 'second', 'handler'], $log);
    }

    /**
     * Asserts that middleware can short-circuit the pipeline by not calling next.
     */
    public function testMiddlewareCanShortCircuitPipeline(): void
    {
        $command = new class () implements CommandInterface {
        };

        $handler = new class () implements CommandHandlerInterface {
            public bool $called = false;

            public function handle(CommandInterface $command): mixed
            {
                $this->called = true;

                return null;
            }
        };

        $middleware = new class () implements CommandMiddlewareInterface {
            public function execute(CommandInterface $command, callable $next): mixed
            {
                return 'short-circuited';
            }
        };

        $this->bus->register(get_class($command), $handler);
        $this->bus->addMiddleware($middleware);
        $result = $this->bus->dispatch($command);

        $this->assertFalse($handler->called);
        $this->assertSame('short-circuited', $result);
    }
}
