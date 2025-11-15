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

namespace Infection\TestFramework\Coverage\PHPUnitXml\File;

use function array_filter;
use function array_map;
use function array_values;
use Infection\TestFramework\XML\SafeDOMXPath;
use function iterator_to_array;

/**
 * Represents a coverage file of the PHPUnit XML coverage report. Typically, this
 * is the `CI/MemoizedCiDetector.php.xml` found in the XML coverage directory
 * for the source file `<project-source>/CI/MemoizedCiDetector.php`.
 *
 * This file contains:
 * - A summary of the executable, executed and covered code.
 * - A breakdown of the source code, its tokens, its namespace, classes, methods, etc.
 * - Information about the coverage: which line is covered and by what test.
 *
 * In Infection, we use this file to know which lines are covered and by what tests.
 */
final class FileReport
{
    private SafeDOMXPath $xPath;

    /**
     * @param string $pathname absolute canonical pathname of the XML coverage file
     */
    public function __construct(
        private readonly string $pathname,
    ) {
    }

    /**
     * This method is not expected to be called if the file has already been
     * identified to not have any tests, i.e. we expect to have at least one
     * line of executable code covered.
     *
     * @return non-empty-list<LineCoverage>
     */
    public function getLineCoverage(): array
    {
        return array_map(
            LineCoverage::fromNode(...),
            iterator_to_array(
                $this->getXPath()->queryList('//coverage:coverage//coverage:line'),
            ),
        );
    }

    /**
     * @return list<MethodLineRange>
     */
    public function getCoveredSourceMethodLineRanges(): array
    {
        return array_values(
            array_filter(
                array_map(
                    MethodLineRange::tryFromNode(...),
                    iterator_to_array(
                        $this->getSourceMethodNodes(),
                    ),
                ),
            ),
        );
    }

    /**
     * If the declaring file is a class with methods, it will contain the node `class.method[n]`.
     * If it is a trait, it will be `trait.method[n]` instead.
     */
    private function getSourceMethodNodes(): iterable
    {
        $count = 0;

        foreach ($this->getXPath()->queryList('//coverage:class//coverage:method') as $node) {
            ++$count;

            yield $node;
        }

        if ($count === 0) {
            yield from $this->getXPath()->queryList('//coverage:trait//coverage:method');
        }
    }

    private function getXPath(): SafeDOMXPath
    {
        return $this->xPath ??= $this->createXPath();
    }

    private function createXPath(): SafeDOMXPath
    {
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
}
