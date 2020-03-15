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

use function array_key_exists;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\NodeLineRangeData;
use Infection\TestFramework\Coverage\SourceMethodRange;
use Infection\TestFramework\Coverage\TestLocations;

/**
 * @internal
 * @final
 */
class TestLocator
{
    private $testLocations;

    public function __construct(TestLocations $testLocations)
    {
        $this->testLocations = $testLocations;
    }

    public function hasTests(): bool
    {
        foreach ($this->testLocations->getTestsLocationsBySourceLine() as $testLocations) {
            if ($testLocations !== []) {
                return true;
            }
        }

        return false;
    }

    public function getAllTestsForMutation(
        NodeLineRangeData $lineRange,
        bool $isOnFunctionSignature
    ): iterable {
        // TODO: would any of those operations benefit from being cached? To be checked with a profile
        if ($isOnFunctionSignature) {
            return $this->getTestsForFunctionSignature($lineRange);
        }

        return $this->getTestsForLineRange($lineRange);
    }

    /**
     * @return iterable<TestLocation>
     */
    private function getTestsForFunctionSignature(NodeLineRangeData $lineRange): iterable
    {
        foreach ($lineRange->range as $line) {
            yield from $this->getTestsForExecutedMethodOnLine($line);
        }
    }

    /**
     * @return iterable<TestLocation>
     */
    private function getTestsForLineRange(NodeLineRangeData $lineRange): iterable
    {
        foreach ($lineRange->range as $line) {
            if (array_key_exists($line, $this->testLocations->getTestsLocationsBySourceLine())) {
                yield from $this->testLocations->getTestsLocationsBySourceLine()[$line];
            }
        }
    }

    /**
     * @return iterable<TestLocation>
     */
    private function getTestsForExecutedMethodOnLine(int $line): iterable
    {
        foreach ($this->testLocations->getSourceMethodRangeByMethod() as $methodRange) {
            /** @var SourceMethodRange $methodRange */
            if (
                $line >= $methodRange->getStartLine()
                && $line <= $methodRange->getEndLine()
            ) {
                return $this->getTestsForLineRange(new NodeLineRangeData(
                    $methodRange->getStartLine(),
                    $methodRange->getEndLine()
                ));
            }
        }

        return [];
    }
}
