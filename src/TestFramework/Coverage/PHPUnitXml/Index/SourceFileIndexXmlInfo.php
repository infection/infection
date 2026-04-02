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

namespace Infection\TestFramework\Coverage\PHPUnitXml\Index;

use DOMElement;
use function str_ends_with;
use function substr;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

/**
 * Represents information about a source file from the index file of the PHPUnit
 * XML coverage report.
 *
 * TODO: to replace SourceFileInfoProvider
 */
final readonly class SourceFileIndexXmlInfo
{
    public function __construct(
        public string $sourcePathname,
        public string $coveragePathname,
        private LinesCoverageSummary $linesCoverageSummary,
    ) {
    }

    public function hasExecutedCode(): bool
    {
        return $this->linesCoverageSummary->executed > 0;
    }

    public static function fromNode(
        DOMElement $node,
        string $coverageDirPathname,
        string $coverageProjectSource,
    ): self {
        $coverageRelativePath = $node->getAttribute('href');
        $coveragePathname = Path::join($coverageDirPathname, $coverageRelativePath);
        Assert::true(str_ends_with($coveragePathname, '.php.xml'));

        $sourcePathname = Path::join(
            $coverageProjectSource,
            substr(
                $coverageRelativePath,
                0,
                -4,
            ),
        );

        $totals = $node->firstElementChild;
        Assert::string('totals', $totals->tagName);

        $lines = $totals->firstElementChild;
        Assert::string('lines', $totals->tagName);

        return new self(
            $sourcePathname,
            $coveragePathname,
            LinesCoverageSummary::fromNode($lines),
        );
    }
}
