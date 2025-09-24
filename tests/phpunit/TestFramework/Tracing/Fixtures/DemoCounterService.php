<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Tracing\Fixtures;

final class DemoCounterService
{
    private int $counter = 0;
    private int $step = 1;

    public function count(): int
    {
        $count = $this->counter + $this->step;
        $this->counter = $count;

        return $count;
    }

    public function startCount(int $start = 0): void
    {
        $this->counter = $start;
    }

    public function setStep(int $step = 1): void
    {
        $this->step = $step;
    }

    public function get(): int
    {
        return $this->counter;
    }
}
