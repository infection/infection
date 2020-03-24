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

namespace Infection\Tests\TestFramework\Coverage;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\CannotBeInstantiated;
use Infection\TestFramework\Coverage\SourceMethodLineRange;
use Infection\TestFramework\Coverage\TestLocations;
use function is_array;
use function is_scalar;
use function iterator_to_array;
use Traversable;

final class TestLocationsNormalizer
{
    use CannotBeInstantiated;

    /**
     * @param TestLocations[]|TestLocation[] $value
     *
     * @return array<string|int, mixed>
     */
    public static function normalize(iterable $value): array
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        return self::serializeValue($value);
    }

    private static function serializeValue($mixed)
    {
        if ($mixed === null) {
            return null;
        }

        if (is_scalar($mixed)) {
            return $mixed;
        }

        if ($mixed instanceof TestLocation) {
            return [
                'testMethod' => $mixed->getMethod(),
                'testFilePath' => $mixed->getFilePath(),
                'testExecutionTime' => $mixed->getExecutionTime(),
            ];
        }

        if ($mixed instanceof SourceMethodLineRange) {
            return [
                'startLine' => $mixed->getStartLine(),
                'endLine' => $mixed->getEndLine(),
            ];
        }

        if ($mixed instanceof TestLocations) {
            return [
                'byLine' => self::serializeValue($mixed->getTestsLocationsBySourceLine()),
                'byMethod' => self::serializeValue($mixed->getSourceMethodRangeByMethod()),
            ];
        }

        if (is_array($mixed)) {
            $convertedArray = [];

            foreach ($mixed as $key => $value) {
                $convertedArray[$key] = self::serializeValue($value);
            }

            return $convertedArray;
        }

        return self::serializeValue((array) $mixed);
    }
}
