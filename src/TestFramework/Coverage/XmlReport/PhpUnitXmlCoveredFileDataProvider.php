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

use Infection\TestFramework\Coverage\SourceFileData;
use Infection\TestFramework\Coverage\SourceFileDataProvider;
use Infection\TestFramework\PhpUnit\Coverage\IndexXmlCoverageParser;
use function Safe\file_get_contents;

/**
 * Source of primary coverage data. Used by SourceFileDataFactory.
 *
 * @internal
 * @final
 *
 * TODO: rename to PhpUnitXmlCoverageTraceProvider: Provides the traces based on the PHPUnit XML coverage collected
 */
class PhpUnitXmlCoveredFileDataProvider implements SourceFileDataProvider
{
    /**
     * TODO: make this constant private
     */
    public const COVERAGE_INDEX_FILE_NAME = 'index.xml';

    private $coverageDir;
    private $parser;

    public function __construct(
        string $coverageDir,
        IndexXmlCoverageParser $coverageXmlParser
    ) {
        $this->coverageDir = $coverageDir;
        $this->parser = $coverageXmlParser;
    }

    /**
     * @return iterable<SourceFileData>
     */
    public function provideFiles(): iterable
    {
        $coverageIndexPath = $this->coverageDir . '/' . self::COVERAGE_INDEX_FILE_NAME;
        $coverageIndexContent = file_get_contents($coverageIndexPath);

        return $this->parser->parse($coverageIndexPath, $coverageIndexContent);
    }
}
