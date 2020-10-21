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

namespace Infection\Tests\Configuration\Entry;

use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use PHPUnit\Framework\TestCase;

final class LogsTest extends TestCase
{
    use LogsAssertions;

    /**
     * @dataProvider valuesProvider
     */
    public function test_it_can_be_instantiated(
        ?string $textLogFilePath,
        ?string $summaryLogFilePath,
        ?string $jsonLogFilePath,
        ?string $debugLogFilePath,
        ?string $perMutatorFilePath,
        ?string $checkstyleFilePath,
        ?Badge $badge
    ): void {
        $logs = new Logs(
            $textLogFilePath,
            $summaryLogFilePath,
            $jsonLogFilePath,
            $debugLogFilePath,
            $perMutatorFilePath,
            $checkstyleFilePath,
            $badge
        );

        $this->assertLogsStateIs(
            $logs,
            $textLogFilePath,
            $summaryLogFilePath,
            $jsonLogFilePath,
            $debugLogFilePath,
            $perMutatorFilePath,
            $checkstyleFilePath,
            $badge
        );
    }

    public function test_it_can_be_instantiated_without_any_values(): void
    {
        $logs = Logs::createEmpty();

        $this->assertLogsStateIs(
            $logs,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );
    }

    public function valuesProvider(): iterable
    {
        yield 'minimal' => [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        ];

        yield 'complete' => [
            'text.log',
            'summary.log',
            'json.log',
            'debug.log',
            'perMutator.log',
            'checkstyle.xml',
            new Badge('master'),
        ];
    }
}
