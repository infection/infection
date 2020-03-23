<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Process;

use Infection\Process\Runner\ProcessBearer;
use LogicException;
use Symfony\Component\Process\Process;

final class FakeProcessBearer implements ProcessBearer
{
    public function getProcess(): Process
    {
        throw new LogicException('Did no expect to be called');
    }

    public function markAsTimedOut(): void
    {
        throw new LogicException('Did no expect to be called');
    }

    public function terminateProcess(): void
    {
        throw new LogicException('Did no expect to be called');
    }
}
