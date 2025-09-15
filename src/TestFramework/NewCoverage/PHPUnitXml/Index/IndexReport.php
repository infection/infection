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

namespace Infection\TestFramework\NewCoverage\PHPUnitXml\Index;

// TODO: rather than converting directly to iterable<SourceFileInfoProvider>, this adds a layer of abstraction to expose the report as a PHP object.
//  Need to be revisted.

use function array_key_exists;
use function dirname;
use DOMElement;
use Generator;
use Infection\TestFramework\XML\SafeDOMXPath;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

/**
 * Represents the index file of the PHPUnit XML coverage report. Typically, this
 * is the `index.xml` file found in the XML coverage directory.
 *
 * This file contains:
 * - The exhaustive list of tests executed and their status.
 * - The exhaustive list of source files configured in the PHPUnit configuration `source`.
 * - For each source file, its base name and the relative (to the coverage XML directory) path to
 * the more detailed XML coverage data for that file.
 * - For each source file or directory, details about the executable, executed and covered code.
 *
 * In Infection, we use this file to extract information about the location of the source file
 * and its more detailed XML coverage report.
 */
final class IndexReport
{
    private readonly string $coverageDirPathname;

    private SafeDOMXPath $xPath;

    /**
     * @var array<string, SourceFileIndexXmlInfo|null>
     */
    private array $indexedFileInfos = [];

    private Generator $fileInfosGenerator;

    private bool $traversed = false;

    private string $source;

    public function __construct(
        private readonly string $pathname,
    ) {
        $this->coverageDirPathname = dirname($pathname);
    }

    /**
     * @param string $sourcePathname Canonical pathname of the source file. It
     *                               is expected to either be absolute, or it
     *                               should be relative to the PHPUnit source
     *                               (configured in the PHPUnit configuration file).
     */
    public function findSourceFileInfo(string $sourcePathname): ?SourceFileIndexXmlInfo
    {
        return array_key_exists($sourcePathname, $this->indexedFileInfos)
            ? $this->indexedFileInfos[$sourcePathname]
            : $this->lookup($sourcePathname);
    }

    /**
     * @param string $sourcePathname Canonical pathname of the source file. It
     *                               is expected to either be absolute, or it
     *                               should be relative to the PHPUnit source
     *                               (configured in the PHPUnit configuration file).
     */
    public function hasTest(string $sourcePathname): bool
    {
        return $this->findSourceFileInfo($sourcePathname)?->hasExecutedCode() ?? false;
    }

    private function lookup(string $sourcePathname): ?SourceFileIndexXmlInfo
    {
        $source = $this->getPhpunitSource();
        $sourcePathIsAbsolute = Path::isAbsolute($sourcePathname);

        // TODO: we can probably shortcut to look for the end of the file
        if (
            $sourcePathIsAbsolute
            && !Path::isBasePath($source, $source)
        ) {
            $this->indexedFileInfos[$sourcePathname] = null;

            return null;
        } elseif (!$sourcePathIsAbsolute) {
            // TODO: check this assumption, but I expect we could have a relative file path here
            //  although it should NOT be a the basename only (as otherwise it would cause issues if there is two files with the same basename).
            // TODO: would the path be different on Windows?
            $correctedSourcePathname = Path::join($source, $sourcePathname);

            if (array_key_exists($correctedSourcePathname, $this->indexedFileInfos)) {
                $fileInfo = $this->indexedFileInfos[$correctedSourcePathname];
                $this->indexedFileInfos[$sourcePathname] = $fileInfo;

                return $fileInfo;
            }
        } else {
            $correctedSourcePathname = $sourcePathname;
        }

        if ($this->traversed) {
            $this->indexedFileInfos[$sourcePathname] = null;
            $this->indexedFileInfos[$correctedSourcePathname] = null;

            return null;
        }

        $fileInfos = $this->getFileInfos();

        // Do not use a foreach loop as it does a rewind which we do not want
        // to do.
        while ($fileInfos->valid()) {
            $fileInfo = $fileInfos->current();
            $fileInfos->next();

            if ($fileInfo->sourcePathname === $correctedSourcePathname) {
                $this->indexedFileInfos[$sourcePathname] = $fileInfo;
                $this->indexedFileInfos[$correctedSourcePathname] = $fileInfo;

                return $fileInfo;
            }

            $this->indexedFileInfos[$fileInfo->sourcePathname] = $fileInfo;

            if ($this->traversed) {
                break;
            }
        }

        $this->indexedFileInfos[$sourcePathname] = null;
        $this->indexedFileInfos[$correctedSourcePathname] = null;

        return null;
    }

    /**
     * @return Generator<string, SourceFileIndexXmlInfo>
     */
    private function getFileInfos(): Generator
    {
        // We keep the generator assigned to resume the traverse rather than
        // starting over.
        if (!isset($this->fileInfosGenerator)) {
            $this->fileInfosGenerator = $this->getFileInfosGenerator();
        }

        return $this->fileInfosGenerator;
    }

    /**
     * @return Generator<SourceFileIndexXmlInfo>
     */
    private function getFileInfosGenerator(): Generator
    {
        $source = $this->getPhpunitSource();
        $files = $this->getXPath()->queryList('//coverage:file');

        foreach ($files as $file) {
            Assert::isInstanceOf($file, DOMElement::class);

            yield SourceFileIndexXmlInfo::fromNode(
                $file,
                $this->coverageDirPathname,
                $source,
            );
        }

        $this->traversed = true;
        unset($this->xPath);
        unset($this->fileInfosGenerator);
    }

    private function getPhpunitSource(): string
    {
        if (!isset($this->source)) {
            $project = $this->getXPath()->queryElement('/coverage:phpunit/coverage:project');
            $this->source = $project->getAttribute('source');
        }

        return $this->source;
    }

    private function getXPath(): SafeDOMXPath
    {
        return $this->xPath ??= $this->createXPath();
    }

    private function createXPath(): SafeDOMXPath
    {
        $this->assertFileWasNotTraversed();

        $xPath = SafeDOMXPath::fromFile($this->pathname);

        // The default PHPUnit namespace is "https://schema.phpunit.de/coverage/1.0".
        // It is quite verbose and would be annoying to use it everywhere.
        // Instead, it is better to introduce an easy to write and read namespace
        // that we can use in the queries.
        $xPath->registerNamespace(
            'coverage',
            $xPath->document->documentElement->namespaceURI,
        );

        return $xPath;
    }

    private function assertFileWasNotTraversed(): void
    {
        Assert::false(
            $this->traversed,
            sprintf(
                'Did not expect to create an XPath for the file "%s": The file was already traversed.',
                $this->pathname,
            ),
        );
    }
}
