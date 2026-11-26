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

namespace Infection\Tests\Environment;

use Infection\Environment\AgentDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AgentDetector::class)]
final class AgentDetectorTest extends TestCase
{
    public static function environmentProvider(): iterable
    {
        yield 'unrelated variables' => [['PATH' => '/usr/bin', 'CI' => 'true'], false];

        yield 'known agent' => [['CLAUDECODE' => '1'], true];

        yield 'agent variable with an empty value' => [['CODEX_SANDBOX' => ''], true];

        yield 'last agent in the list' => [['REPL_ID' => 'x'], true];

        yield 'generic AI_AGENT' => [['AI_AGENT' => 'my-agent'], true];

        yield 'NOT_AI_AGENT alone' => [['NOT_AI_AGENT' => ''], false];

        yield 'NOT_AI_AGENT with a known agent' => [['NOT_AI_AGENT' => '', 'CLAUDECODE' => '1'], false];
    }

    /**
     * @param array<string, string> $environment
     */
    #[DataProvider('environmentProvider')]
    public function test_it_detects_an_agent_harness(array $environment, bool $expected): void
    {
        $this->assertSame($expected, (new AgentDetector())->isRunningInAgent($environment));
    }
}
