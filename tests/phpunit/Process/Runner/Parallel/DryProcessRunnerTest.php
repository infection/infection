<?php

declare(strict_types=1);

namespace Infection\Tests\Process\Runner\Parallel;

use Infection\Process\Runner\Parallel\DryProcessRunner;
use Infection\Tests\Fixtures\Process\FakeProcessBearer;
use PHPUnit\Framework\TestCase;

final class DryProcessRunnerTest extends TestCase
{
    public function test_it_can_iterate_over_the_processes(): void
    {
        $called = false;

        $processes = (static function () use (&$called) {
            yield new FakeProcessBearer();

            $called = true;

            yield new FakeProcessBearer();
        })();

        (new DryProcessRunner())->run($processes);

        $this->assertTrue($called);
    }
}
