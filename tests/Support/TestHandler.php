<?php

declare(strict_types=1);

namespace AndrewDyer\CommandBus\Tests\Support;

/**
 * A plain handler for use in tests that records whether it was called.
 */
final class TestHandler
{
    /**
     * Indicates whether the handler was invoked.
     */
    public bool $called = false;

    /**
     * Handles the given command and marks the handler as called.
     *
     * @param TestCommand $command The command to handle.
     * @return null
     */
    public function handle(TestCommand $command): null
    {
        $this->called = true;

        return null;
    }
}
