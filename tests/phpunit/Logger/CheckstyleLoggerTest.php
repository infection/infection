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

use Infection\Logger\CheckstyleLogger;
use Infection\Metrics\MetricsCalculator;
use PHPUnit\Framework\TestCase;

final class CheckstyleLoggerTest extends TestCase
{
    use CreateMetricsCalculator;

    /**
     * @dataProvider metricsProvider
     */
    public function test_it_logs_correctly_with_mutations(
        MetricsCalculator $metricsCalculator,
        string $expectedContents
    ): void {
        $logger = new CheckstyleLogger($metricsCalculator);

        $this->assertLoggedContentIs($expectedContents, $logger);
    }

    public function metricsProvider(): iterable
    {
        yield 'no mutations' => [
            new MetricsCalculator(2),
            <<<XML
<?xml version="1.0"?>
<checkstyle version="6.5"/>
XML
        ];

        yield 'all mutations' => [
            $this->createCompleteMetricsCalculator(),
            <<<XML
<?xml version="1.0"?>
<checkstyle version="6.5">
  <file name="foo/bar">
    <error line="9" message="Escaped Mutant:&#10;&#10;--- Original&#10;+++ New&#10;@@ @@&#10;&#10;- echo 'original';&#10;+ echo 'escaped#1';&#10;" severity="warning" source="PregQuote"/>
    <error line="10" message="Escaped Mutant:&#10;&#10;--- Original&#10;+++ New&#10;@@ @@&#10;&#10;- echo 'original';&#10;+ echo 'escaped#0';&#10;" severity="warning" source="For_"/>
  </file>
</checkstyle>
XML
            ,
        ];
    }

    private function assertLoggedContentIs(string $expectedXml, CheckstyleLogger $logger): void
    {
        $this->assertXmlStringEqualsXmlString($expectedXml, $logger->getLogLines()[0]);
    }
}
