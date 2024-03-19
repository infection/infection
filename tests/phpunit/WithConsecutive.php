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

namespace Infection\Tests;

use function array_shift;
use function count;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use RuntimeException;

/**
 * Usage: ->with(...WithConsecutive::create(...$withCodes))
 *
 * Originally copied from https://gist.github.com/oleg-andreyev/85c74dbf022237b03825c7e9f4439303 and modified
 * See also https://github.com/sebastianbergmann/phpunit/issues/4026#issuecomment-1644072411
 */
class WithConsecutive
{
    /**
     * @param array<mixed> $parameterGroups
     *
     * @return array<int, callback<mixed>>
     */
    public static function create(...$parameterGroups): array
    {
        $result = [];
        $parametersCount = null;
        $groups = [];
        $values = [];

        foreach ($parameterGroups as $index => $parameters) {
            // initial
            $parametersCount ??= count($parameters);

            // compare
            if ($parametersCount !== count($parameters)) {
                throw new RuntimeException('Parameters count max much in all groups');
            }

            // prepare parameters
            foreach ($parameters as $parameter) {
                if (!$parameter instanceof Constraint) {
                    $parameter = new IsEqual($parameter);
                }

                $groups[$index][] = $parameter;
            }
        }

        // collect values
        foreach ($groups as $parameters) {
            foreach ($parameters as $index => $parameter) {
                $values[$index][] = $parameter;
            }
        }

        // build callback
        for ($index = 0; $index < $parametersCount; ++$index) {
            $result[$index] = Assert::callback(static function ($value) use ($values, $index) {
                static $map = null;
                $map ??= $values[$index];

                $expectedArg = array_shift($map);

                if ($expectedArg !== null) {
                    $expectedArg->evaluate($value);
                }

                return true;
            });
        }

        return $result;
    }
}
