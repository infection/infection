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

namespace Infection\Tests\Report\Framework\Factory;

use Infection\Report\AggregateReporter;
use Infection\Report\Framework\Factory\AggregateReporterFactory;
use Infection\Report\Framework\Factory\ReporterFactory;
use Infection\Tests\Configuration\Entry\LogsBuilder;
use Infection\Tests\Reporter\FakeReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AggregateReporterFactory::class)]
final class AggregateReporterFactoryTest extends TestCase
{
    public function test_it_creates_an_aggregate_reporter(): void
    {
        $reporter1 = new FakeReporter();
        $reporter2 = new FakeReporter();

        $expected = new AggregateReporter([$reporter1, $reporter2]);

        $config = LogsBuilder::withMinimalTestData()->build();

        $factoryMock1 = $this->createMock(ReporterFactory::class);
        $factoryMock1
            ->expects($this->once())
            ->method('create')
            ->with($this->identicalTo($config))
            ->willReturn($reporter1);

        $factoryMock2 = $this->createMock(ReporterFactory::class);
        $factoryMock2
            ->expects($this->once())
            ->method('create')
            ->with($this->identicalTo($config))
            ->willReturn($reporter2);

        $factory = new AggregateReporterFactory([$factoryMock1, $factoryMock2]);
        $actual = $factory->create($config);

        $this->assertEquals($expected, $actual);
    }
}
