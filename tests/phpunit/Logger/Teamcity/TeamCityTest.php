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

namespace Infection\Tests\Logger\Teamcity;

use Infection\Logger\Teamcity\MessageName;
use Infection\Logger\Teamcity\TeamCity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TeamCity::class)]
final class TeamCityTest extends TestCase
{
    private TeamCity $teamcity;

    protected function setUp(): void
    {
        $this->teamcity = new TeamCity();
    }

    #[DataProvider('messageProvider')]
    public function test_it_can_write_a_message(
        MessageName $messageName,
        string|array $value,
        string $expected,
    ): void {
        $actual = $this->teamcity->write(
            $messageName,
            $value,
        );

        $this->assertSame($expected, $actual);
    }

    public static function messageProvider(): iterable
    {
        yield 'single-attribute message' => [
            MessageName::FLOW_STARTED,
            'value',
            "##teamcity[flowStarted 'value']",
        ];

        yield 'multiple-attribute message' => [
            MessageName::FLOW_STARTED,
            ['name1' => 'value1', 'name2' => 'value2'],
            "##teamcity[flowStarted name1='value1' name2='value2']",
        ];

        yield '[escape] apostrophe' => [
            MessageName::FLOW_STARTED,
            "'",
            "##teamcity[flowStarted '|'']",
        ];

        yield '[escape] line feed' => [
            MessageName::FLOW_STARTED,
            "\n",
            "##teamcity[flowStarted '|n']",
        ];

        yield '[escape] carriage return' => [
            MessageName::FLOW_STARTED,
            "\r",
            "##teamcity[flowStarted '|r']",
        ];

        yield '[escape] vertical bar' => [
            MessageName::FLOW_STARTED,
            '|',
            "##teamcity[flowStarted '||']",
        ];

        yield '[escape] opening bracket' => [
            MessageName::FLOW_STARTED,
            '[',
            "##teamcity[flowStarted '|[']",
        ];

        yield '[escape] closing bracket' => [
            MessageName::FLOW_STARTED,
            ']',
            "##teamcity[flowStarted '|]']",
        ];

        yield '[escape] message with escaped characters' => [
            MessageName::FLOW_STARTED,
            '\'\u99AA[||]\u00FF',
            "##teamcity[flowStarted '|'|0x99AA|[|||||]|0x00FF']",
        ];
    }
}
