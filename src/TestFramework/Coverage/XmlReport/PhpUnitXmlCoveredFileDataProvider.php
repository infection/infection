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

use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\CoveredFileData;
use Infection\TestFramework\Coverage\CoveredFileDataProvider;
use Infection\TestFramework\PhpUnit\Coverage\IndexXmlCoverageParser;
use function Safe\file_get_contents;

/**
 * Source of primary coverage data. Used by CoveredFileDataFactory.
 *
 * @see CoveredFileDataFactory
 *
 * @internal
 * @final
 */
class PhpUnitXmlCoveredFileDataProvider implements CoveredFileDataProvider
{
    /**
     * TODO: make this constant private
     */
    public const COVERAGE_INDEX_FILE_NAME = 'index.xml';

    private $coverageDir;
    private $parser;
    private $testFrameworkKey;

    public function __construct(
        string $coverageDir,
        IndexXmlCoverageParser $coverageXmlParser,
        string $testFrameworkKey
    ) {
        $this->coverageDir = $coverageDir;
        $this->parser = $coverageXmlParser;
        $this->testFrameworkKey = $testFrameworkKey;
    }

    /**
     * @throws CoverageDoesNotExistException
     *
     * @return iterable<CoveredFileData>
     */
    public function provideFiles(): iterable
    {
        $coverageIndexFileContent = file_get_contents(
            $this->coverageDir . '/' . self::COVERAGE_INDEX_FILE_NAME
        );

        return $this->parser->parseLazy($coverageIndexFileContent);
    }
}
