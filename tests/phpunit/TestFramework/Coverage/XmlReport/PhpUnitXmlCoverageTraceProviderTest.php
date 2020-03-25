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

use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageTraceProvider;
use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\Tests\FileSystem\FileSystemTestCase;
use function Safe\file_put_contents;

/**
 * @group integration
 */
final class PhpUnitXmlCoverageTraceProviderTest extends FileSystemTestCase
{
    public function test_it_can_parse_coverage_data(): void
    {
        $indexPath = $this->tmp . '/index.xml';
        $indexContents = 'index contents';

        file_put_contents($indexPath, $indexContents);

        $indexLocatorMock = $this->createMock(IndexXmlCoverageLocator::class);
        $indexLocatorMock
            ->method('locate')
            ->willReturn($indexPath)
        ;

        $sourceFileInfoProviderMock = $this->createMock(SourceFileInfoProvider::class);

        $indexXmlParserMock = $this->createMock(IndexXmlCoverageParser::class);
        $indexXmlParserMock
            ->method('parse')
            ->with($indexPath, $indexContents)
            ->willReturn([$sourceFileInfoProviderMock])
        ;

        $traceMock = $this->createMock(Trace::class);
        $traceMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $coverageXmlParserMock = $this->createMock(XmlCoverageParser::class);
        $coverageXmlParserMock
            ->method('parse')
            ->with($sourceFileInfoProviderMock)
            ->willReturn($traceMock)
        ;

        $provider = new PhpUnitXmlCoverageTraceProvider(
            $indexLocatorMock,
            $indexXmlParserMock,
            $coverageXmlParserMock
        );

        $traces = $provider->provideTraces();

        $this->assertSame([$traceMock], iterator_to_array($traces, true));
    }
}
