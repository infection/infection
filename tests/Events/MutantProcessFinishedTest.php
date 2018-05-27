<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Events;

use Infection\Events\MutantProcessFinished;
use Infection\Process\MutantProcessInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutantProcessFinishedTest extends TestCase
{
    public function test_it_passes_around_its_mutant_process_without_changing_it()
    {
        $process = $this->createMock(MutantProcessInterface::class);
        $process->expects($this->never())->method($this->anything());

        $event = new MutantProcessFinished($process);
        $this->assertSame($process, $event->getMutantProcess());
    }
}
