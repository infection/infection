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

namespace Infection\TestFramework\PhpUnit\Coverage;

use function array_filter;
use function implode;
use Infection\TestFramework\SafeDOMXPath;
use function realpath as native_realpath;
use function Safe\file_get_contents;
use function Safe\sprintf;
use function str_replace;
use Symfony\Component\Finder\SplFileInfo;
use function trim;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

/**
 * @internal
 * @final
 */
class SourceFileInfoProvider
{
    /**
     * @var ?SafeDOMXPath
     */
    private $xPath;
    private $coverageDir;
    private $relativeCoverageFilePath;
    private $projectSource;

    public function __construct(
        string $coverageDir,
        string $relativeCoverageFilePath,
        string $projectSource
    ) {
        $this->coverageDir = $coverageDir;
        $this->relativeCoverageFilePath = $relativeCoverageFilePath;
        $this->projectSource = $projectSource;
    }

    /**
     * @throws InvalidCoverage
     */
    public function provideFileInfo(): SplFileInfo
    {
        return $this->retrieveSourceFileInfo($this->provideXPath());
    }

    public function provideXPath(): SafeDOMXPath
    {
        if ($this->xPath === null) {
            $this->xPath = XPathFactory::createXPath(file_get_contents(
                $this->coverageDir . '/' . $this->relativeCoverageFilePath
            ));
        }

        return $this->xPath;
    }

    private function retrieveSourceFileInfo(SafeDOMXPath $xPath): SplFileInfo
    {
        $fileNode = $xPath->query('/phpunit/file')[0];

        Assert::notNull($fileNode);

        $fileName = $fileNode->getAttribute('name');
        $relativeFilePath = $fileNode->getAttribute('path');

        if ($relativeFilePath === '') {
            // The relative path is not present for old versions of PHPUnit. As a result we parse
            // the relative path from the source file path and the XML coverage file
            $relativeFilePath = str_replace(
                sprintf('%s.xml', $fileName),
                '',
                $this->relativeCoverageFilePath
            );
        }

        $path = implode(
            '/',
            array_filter([
                $this->projectSource,
                trim($relativeFilePath, '/'),
                $fileName,
            ])
        );

        $realPath = native_realpath($path);

        if ($realPath === false) {
            $coverageFilePath = Path::canonicalize(
                $this->coverageDir . DIRECTORY_SEPARATOR . $this->relativeCoverageFilePath
            );

            throw new InvalidCoverage(sprintf(
                'Could not find the source file "%s" referred by "%s". Make sure the '
                . 'coverage used is up to date',
                $path,
                $coverageFilePath
            ));
        }

        return new SplFileInfo($realPath, $relativeFilePath, $path);
    }
}
