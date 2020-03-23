<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Process;

use Infection\Process\Runner\ProcessBearer;
use Infection\Tests\UnsupportedMethod;
use Symfony\Component\Process\Process;

final class FakeProcessBearer implements ProcessBearer
{
    public function getProcess(): Process
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function markAsTimedOut(): void
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function terminateProcess(): void
    {
        throw new LogicException('Did no expect to be called');
    }
}
