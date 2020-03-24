<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Logger;

use Infection\Logger\DebugFileLogger;
use Infection\Metrics\MetricsCalculator;
use PHPUnit\Framework\TestCase;

final class DebugFileLoggerTest extends TestCase
{
    use CreateMetricsCalculator;
    use LineLoggerAssertions;

    /**
     * @dataProvider metricsProvider
     */
    public function test_it_logs_correctly_with_mutations(
        MetricsCalculator $metricsCalculator,
        bool $onlyCoveredMode,
        string $expectedContents
    ): void {
        $logger = new DebugFileLogger($metricsCalculator, $onlyCoveredMode);

        $this->assertLoggedContentIs($expectedContents, $logger);
    }

    public function metricsProvider(): iterable
    {
        yield 'no mutations' => [
            new MetricsCalculator(),
            false,
            <<<'TXT'
Total: 0

Killed mutants:
===============

Errors mutants:
===============

Escaped mutants:
================

Timed Out mutants:
==================

Not Covered mutants:
====================

TXT
        ];

        yield 'all mutations' => [
            $this->createCompleteMetricsCalculator(),
            false,
            <<<'TXT'
Total: 10

Killed mutants:
===============

Mutator: PregQuote
Line 9

Mutator: For_
Line 10


Errors mutants:
===============

Mutator: PregQuote
Line 9

Mutator: For_
Line 10


Escaped mutants:
================

Mutator: PregQuote
Line 9

Mutator: For_
Line 10


Timed Out mutants:
==================

Mutator: PregQuote
Line 9

Mutator: For_
Line 10


Not Covered mutants:
====================

Mutator: PregQuote
Line 9

Mutator: For_
Line 10

TXT
        ];

        yield 'all mutations only covered' => [
            $this->createCompleteMetricsCalculator(),
            true,
            <<<'TXT'
Total: 10

Killed mutants:
===============

Mutator: PregQuote
Line 9

Mutator: For_
Line 10


Errors mutants:
===============

Mutator: PregQuote
Line 9

Mutator: For_
Line 10


Escaped mutants:
================

Mutator: PregQuote
Line 9

Mutator: For_
Line 10


Timed Out mutants:
==================

Mutator: PregQuote
Line 9

Mutator: For_
Line 10

TXT
        ];
    }
}
