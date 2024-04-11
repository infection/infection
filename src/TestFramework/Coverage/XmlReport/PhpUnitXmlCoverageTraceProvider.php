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

namespace Infection\TestFramework\Coverage\XmlReport;

use function dirname;
use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\Coverage\TraceProvider;
use function Safe\file_get_contents;

/**
 * Provides the traces based on the PHPUnit XML coverage collected.
 *
 * @internal
 * @final
 */
class PhpUnitXmlCoverageTraceProvider implements TraceProvider
{
    public function __construct(private readonly IndexXmlCoverageLocator $indexLocator, private readonly IndexXmlCoverageParser $indexParser, private readonly XmlCoverageParser $parser)
    {
    }

    /**
     * @return iterable<Trace>
     */
    public function provideTraces(): iterable
    {
        // The existence of the file should have already been checked. Hence in theory we should not
        // have to deal with a FileNotFound exception here so we skip any friendly error handling
        $indexPath = $this->indexLocator->locate();
        $coverageBasePath = dirname($indexPath);
        $indexContents = file_get_contents($indexPath);

        foreach ($this->indexParser->parse(
            $indexPath,
            $indexContents,
            $coverageBasePath,
        ) as $infoProvider) {
            // TODO It might be beneficial to filter files at this stage, rather than later. SourceFileDataFactory does that.
            yield $this->parser->parse($infoProvider);
        }
    }
}
