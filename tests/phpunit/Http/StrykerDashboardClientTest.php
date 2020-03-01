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

namespace Infection\Tests\Http;

use Infection\Http\JsonClient;
use Infection\Http\Response;
use Infection\Http\StrykerDashboardClient;
use Infection\Tests\Logger\DummyLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use function Safe\json_decode;
use function Safe\json_encode;

final class StrykerDashboardClientTest extends TestCase
{
    /**
     * @var JsonClient|MockObject
     */
    private $clientMock;

    /**
     * @var DummyLogger
     */
    private $logger;

    /**
     * @var StrykerDashboardClient
     */
    private $dashboardClient;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(JsonClient::class);
        $this->logger = new DummyLogger();

        $this->dashboardClient = new StrykerDashboardClient(
            $this->clientMock,
            $this->logger
        );
    }

    public function test_it_can_send_a_report(): void
    {
        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                'https://dashboard.stryker-mutator.io/api/reports',
                json_encode(json_decode(<<<'JSON'
{
    "apiKey": "foo",
    "repositorySlug": "infection/infection",
    "branch": "master",
    "mutationScore": 80.31
}
JSON
                ))
            )
            ->willReturn(new Response('Report received!', 201))
        ;

        $this->dashboardClient->sendReport(
            'foo',
            'infection/infection',
            'master',
            80.31
        );

        $this->assertSame(
            [
                [
                    LogLevel::NOTICE,
                    <<<'EOF'
Dashboard response:
Report received!
EOF
                    ,
                    [],
                ],
            ],
            $this->logger->getLogs()
        );
    }

    public function test_it_issues_a_warning_when_the_report_could_not_be_sent(): void
    {
        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                'https://dashboard.stryker-mutator.io/api/reports',
                json_encode(json_decode(<<<'JSON'
{
    "apiKey": "foo",
    "repositorySlug": "infection/infection",
    "branch": "master",
    "mutationScore": 80.31
}
JSON
                ))
            )
            ->willReturn(new Response('Report invalid!', 400))
        ;

        $this->dashboardClient->sendReport(
            'foo',
            'infection/infection',
            'master',
            80.31
        );

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Stryker dashboard returned an unexpected response code: 400',
                    [],
                ],
                [
                    LogLevel::NOTICE,
                    <<<'EOF'
Dashboard response:
Report invalid!
EOF
                    ,
                    [],
                ],
            ],
            $this->logger->getLogs()
        );
    }
}
