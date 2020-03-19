<?php

declare(strict_types=1);

namespace Infection\Process\Runner;

final class DryProcessRunner implements ProcessRunner
{
    public function run(iterable $processes): void
    {
        foreach ($processes as $_) {
            // Do nothing: we just want to make sure we trigger the iterable
        }
    }
}
