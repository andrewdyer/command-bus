<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Tests\Middleware;

use AndrewDyer\CommandBus\Contracts\CommandInterface;
use AndrewDyer\CommandBus\Middleware\LoggingMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LoggingMiddlewareTest extends TestCase
{
    public function testLogsCommandBeforeAndAfterDispatch(): void
    {
        $command = new class () implements CommandInterface {
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

    public function testReturnsResultFromNextCallable(): void
    {
        $command = new class () implements CommandInterface {
        };
        $logger = $this->createMock(LoggerInterface::class);

        $middleware = new LoggingMiddleware($logger);
        $result = $middleware->execute($command, fn () => 'expected result');

        $this->assertSame('expected result', $result);
    }

    public function testPassesCommandToNextCallable(): void
    {
        $command = new class () implements CommandInterface {
        };
        $logger = $this->createMock(LoggerInterface::class);
        $receivedCommand = null;

        $middleware = new LoggingMiddleware($logger);
        $middleware->execute($command, function(CommandInterface $cmd) use (&$receivedCommand) {
            $receivedCommand = $cmd;
        });

        $this->assertSame($command, $receivedCommand);
    }
}
