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

namespace Infection\Tests\TestFramework\Coverage\XmlReport;

use Generator;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\Coverage\XmlReport\TestFileDataProvider;
use Infection\TestFramework\Coverage\XmlReport\XMLLineCodeCoverageFactory;
use Infection\TestFramework\PhpUnit\Coverage\IndexXmlCoverageParser;
use Infection\TestFramework\TestFrameworkTypes;
use PHPUnit\Framework\TestCase;

final class XMLLineCodeCoverageFactoryTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function test_it_can_create_an_XMLLine_code_coverage_instance(
        string $frameworkKey,
        bool $jUnitReport
    ): void {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('hasJUnitReport')
            ->willReturn($jUnitReport)
        ;

        // We cannot test much of the generated instance here since it does not exposes any state.
        // We can only ensure that an instance is created in all scenarios
        (new XMLLineCodeCoverageFactory(
            '/path/to/coverage/dir',
            $this->createMock(IndexXmlCoverageParser::class),
            $this->createMock(TestFileDataProvider::class)
        ))->create($frameworkKey, $adapter);

        $this->addToAssertionCount(1);
    }

    public function valueProvider(): Generator
    {
        foreach (TestFrameworkTypes::TYPES as $frameworkKey) {
            foreach ([true, false] as $jUnitReport) {
                yield [$frameworkKey, $jUnitReport];
            }
        }
    }
}
