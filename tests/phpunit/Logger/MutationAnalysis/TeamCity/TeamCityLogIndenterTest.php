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

namespace Infection\Tests\Logger\MutationAnalysis\TeamCity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TeamCityLogIndenter::class)]
final class TeamCityLogIndenterTest extends TestCase
{
    #[DataProvider('logIndenterProvider')]
    public function test_it_indents_the_logs(
        string $logs,
        string $expected,
    ): void {
        $actual = TeamCityLogIndenter::indent($logs);

        $this->assertSame($expected, $actual);
    }

    public static function logIndenterProvider(): iterable
    {
        yield 'no-op' => [
            <<<'TEAM_CITY'
                ##teamcity[<messageName> 'value']
                ##teamcity[<messageName> 'value']
                TEAM_CITY,
            <<<'TEAM_CITY'
                ##teamcity[<messageName> 'value']
                ##teamcity[<messageName> 'value']
                TEAM_CITY,
        ];

        yield 'nested blocks' => [
            <<<'TEAM_CITY'
                ##teamcity[blockOpened name='block 1']
                ##teamcity[blockOpened name='block 2']
                ##teamcity[message text='Message 1']
                ##teamcity[message text='Message 2']
                ##teamcity[blockClosed name='block 2']
                ##teamcity[blockClosed name='block 1']
                TEAM_CITY,
            <<<'TEAM_CITY'
                ##teamcity[blockOpened name='block 1']
                    ##teamcity[blockOpened name='block 2']
                        ##teamcity[message text='Message 1']
                        ##teamcity[message text='Message 2']
                    ##teamcity[blockClosed name='block 2']
                ##teamcity[blockClosed name='block 1']
                TEAM_CITY,
        ];

        yield 'another nested blocks' => [
            <<<'TEAM_CITY'
                ##teamcity[blockOpened name='block 1']
                ##teamcity[blockOpened name='block 2']
                ##teamcity[message text='Message 1']
                ##teamcity[message text='Message 2']
                ##teamcity[blockClosed name='block 2']
                ##teamcity[blockClosed name='block 1']
                TEAM_CITY,
            <<<'TEAM_CITY'
                ##teamcity[blockOpened name='block 1']
                    ##teamcity[blockOpened name='block 2']
                        ##teamcity[message text='Message 1']
                        ##teamcity[message text='Message 2']
                    ##teamcity[blockClosed name='block 2']
                ##teamcity[blockClosed name='block 1']
                TEAM_CITY,
        ];

        yield 'multiple messages with missing closing messages' => [
            <<<'TEAM_CITY'
                ##teamcity[blockOpened name='block 1']
                ##teamcity[blockOpened name='block 2']
                ##teamcity[message text='Message 1']
                ##teamcity[message text='Message 2']
                TEAM_CITY,
            <<<'TEAM_CITY'
                ##teamcity[blockOpened name='block 1']
                    ##teamcity[blockOpened name='block 2']
                        ##teamcity[message text='Message 1']
                        ##teamcity[message text='Message 2']
                TEAM_CITY,
        ];

        yield 'another example from the documentation' => [
            <<<'TEAM_CITY'
                ##teamcity[flowStarted flowId='MainFlow' ...]
                ##teamcity[flowStarted flowId='SubFlow1' parent='MainFlow' ...]
                ##teamcity[flowFinished flowId='SubFlow1' ...]
                ##teamcity[flowStarted flowId='SubFlow2' parent='MainFlow' ...]
                ##teamcity[flowFinished flowId='SubFlow2' ...]
                ##teamcity[flowFinished flowId='MainFlow' ...]
                TEAM_CITY,
            <<<'TEAM_CITY'
                ##teamcity[flowStarted flowId='MainFlow' ...]
                    ##teamcity[flowStarted flowId='SubFlow1' parent='MainFlow' ...]
                    ##teamcity[flowFinished flowId='SubFlow1' ...]
                    ##teamcity[flowStarted flowId='SubFlow2' parent='MainFlow' ...]
                    ##teamcity[flowFinished flowId='SubFlow2' ...]
                ##teamcity[flowFinished flowId='MainFlow' ...]
                TEAM_CITY,
        ];

        yield 'complete example from the documentation' => [
            <<<'TEAM_CITY'
                ##teamcity[testSuiteStarted name='Test Suite A']
                ##teamcity[testStarted name='Test 1.A' captureStandardOutput='false']
                ##teamcity[flowStarted flowId='mainFlow-1a']
                ##teamcity[testStarted name='Test 1.A, Subtest 1' captureStandardOutput='false']
                ##teamcity[flowStarted flowId='subFlow1-1a' parent='mainFlow-1a']
                ##teamcity[message text='Message 1']
                ##teamcity[flowFinished flowId='subFlow1-1a']
                ##teamcity[testFinished name='Test 1.A, Subtest 1' duration='1000']
                ##teamcity[testStarted name='Test 1.A, Subtest 2' captureStandardOutput='false']
                ##teamcity[flowStarted flowId='subFlow2-1a' parent='mainFlow-1a']
                ##teamcity[flowFinished flowId='subFlow2-1a']
                ##teamcity[testFinished name='Test 1.A, Subtest 2' duration='1000']
                ##teamcity[flowFinished flowId='mainFlow-1a']
                ##teamcity[testFinished name='Test 1.A' duration='3000']
                ##teamcity[testSuiteFinished name='Test Suite A']
                TEAM_CITY,
            <<<'TEAM_CITY'
                ##teamcity[testSuiteStarted name='Test Suite A']
                    ##teamcity[testStarted name='Test 1.A' captureStandardOutput='false']
                        ##teamcity[flowStarted flowId='mainFlow-1a']
                            ##teamcity[testStarted name='Test 1.A, Subtest 1' captureStandardOutput='false']
                                ##teamcity[flowStarted flowId='subFlow1-1a' parent='mainFlow-1a']
                                    ##teamcity[message text='Message 1']
                                ##teamcity[flowFinished flowId='subFlow1-1a']
                            ##teamcity[testFinished name='Test 1.A, Subtest 1' duration='1000']
                            ##teamcity[testStarted name='Test 1.A, Subtest 2' captureStandardOutput='false']
                                ##teamcity[flowStarted flowId='subFlow2-1a' parent='mainFlow-1a']
                                ##teamcity[flowFinished flowId='subFlow2-1a']
                            ##teamcity[testFinished name='Test 1.A, Subtest 2' duration='1000']
                        ##teamcity[flowFinished flowId='mainFlow-1a']
                    ##teamcity[testFinished name='Test 1.A' duration='3000']
                ##teamcity[testSuiteFinished name='Test Suite A']
                TEAM_CITY,
        ];
    }
}
