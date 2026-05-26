<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Tests\Middleware;

use AndrewDyer\CommandBus\Middleware\LoggingMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for LoggingMiddleware.
 */
final class LoggingMiddlewareTest extends TestCase
{
    /**
     * Asserts that the middleware logs command dispatch before and completion after execution.
     */
    public function testLogsCommandBeforeAndAfterDispatch(): void
    {
        $command = new class () {
        };
        $commandClass = get_class($command);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function(string $message) use ($commandClass, &$log) {
                $log[] = $message;
            });

        $middleware = new LoggingMiddleware($logger);
        $middleware->execute($command, fn () => null);

        $this->assertSame(
            [
                "Dispatching command: {$commandClass}",
                "Command handled: {$commandClass}",
            ],
            $log
        );
    }

    /**
     * Asserts that the middleware returns the result from the next callable.
     */
    public function testReturnsResultFromNextCallable(): void
    {
        $command = new class () {
        };
        $logger = $this->createMock(LoggerInterface::class);

        $middleware = new LoggingMiddleware($logger);
        $result = $middleware->execute($command, fn () => 'expected result');

        $this->assertSame('expected result', $result);
    }

    /**
     * Asserts that the middleware passes the command to the next callable.
     */
    public function testPassesCommandToNextCallable(): void
    {
        $command = new class () {
        };
        $logger = $this->createMock(LoggerInterface::class);
        $receivedCommand = null;

        $middleware = new LoggingMiddleware($logger);
        $middleware->execute($command, function(object $cmd) use (&$receivedCommand) {
            $receivedCommand = $cmd;
        });

        $this->assertSame($command, $receivedCommand);
    }
}
