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

use function getmypid;
use Infection\Logger\MutationAnalysis\TeamCity\TeamCity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(TeamCity::class)]
final class AiTeamCityTest extends TestCase
{
    private TeamCity $teamcity;

    protected function setUp(): void
    {
        $this->teamcity = new TeamCity();
    }

    public function test_it_formats_test_suite_started(): void
    {
        $message = $this->teamcity->testSuiteStarted('My Suite');

        $this->assertSame(
            sprintf("##teamcity[testSuiteStarted name='My Suite' flowId='%d']\n", getmypid()),
            $message,
        );
    }

    public function test_it_formats_test_suite_finished(): void
    {
        $message = $this->teamcity->testSuiteFinished('My Suite');

        $this->assertSame(
            sprintf("##teamcity[testSuiteFinished name='My Suite' flowId='%d']\n", getmypid()),
            $message,
        );
    }

    public function test_it_formats_test_count(): void
    {
        $message = $this->teamcity->testCount(42);

        $this->assertSame(
            sprintf("##teamcity[testCount count='42' flowId='%d']\n", getmypid()),
            $message,
        );
    }

    public function test_it_formats_test_started(): void
    {
        $message = $this->teamcity->testStarted('my_test');

        $this->assertSame(
            sprintf("##teamcity[testStarted name='my_test' flowId='%d']\n", getmypid()),
            $message,
        );
    }

    public function test_it_formats_test_finished_without_duration(): void
    {
        $message = $this->teamcity->testFinished('my_test');

        $this->assertSame(
            sprintf("##teamcity[testFinished name='my_test' duration='0' flowId='%d']\n", getmypid()),
            $message,
        );
    }

    public function test_it_formats_test_finished_with_duration(): void
    {
        $message = $this->teamcity->testFinished('my_test', 1234);

        $this->assertSame(
            sprintf("##teamcity[testFinished name='my_test' duration='1234' flowId='%d']\n", getmypid()),
            $message,
        );
    }

    public function test_it_formats_test_failed(): void
    {
        $message = $this->teamcity->testFailed('my_test', 'Something went wrong', 'Stack trace here');

        $this->assertSame(
            sprintf("##teamcity[testFailed name='my_test' message='Something went wrong' details='Stack trace here' flowId='%d']\n", getmypid()),
            $message,
        );
    }

    public function test_it_formats_test_ignored(): void
    {
        $message = $this->teamcity->testIgnored('my_test', 'Skipped because of reason');

        $this->assertSame(
            sprintf("##teamcity[testIgnored name='my_test' message='Skipped because of reason' flowId='%d']\n", getmypid()),
            $message,
        );
    }

    public function test_it_escapes_special_characters(): void
    {
        $message = $this->teamcity->testFailed(
            "test'name",
            "message|with[special]chars\nand\rnewlines",
            'details',
        );

        $this->assertSame(
            sprintf("##teamcity[testFailed name='test|'name' message='message||with|[special|]chars|nand|rnewlines' details='details' flowId='%d']\n", getmypid()),
            $message,
        );
    }
}
