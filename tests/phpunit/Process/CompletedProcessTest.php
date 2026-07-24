<?php

declare(strict_types=1);

namespace Infection\Tests\Process;

use Infection\Process\CompletedProcess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompletedProcess::class)]
final class CompletedProcessTest extends TestCase
{
    public function test_it_exposes_the_process_result(): void
    {
        $process = new CompletedProcess(
            ['php', '-v'],
            0,
            'stdout',
            'stderr',
        );

        $this->assertSame(['php', '-v'], $process->command);
        $this->assertSame(0, $process->exitCode);
        $this->assertSame('stdout', $process->stdout);
        $this->assertSame('stderr', $process->stderr);
        $this->assertTrue($process->isSuccessful());
    }

    public function test_it_can_detect_a_failure(): void
    {
        $process = new CompletedProcess(
            ['php', '-v'],
            1,
            '',
            'stderr',
        );

        $this->assertFalse($process->isSuccessful());
    }
}
