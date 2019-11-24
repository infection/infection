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

namespace Infection\Tests\AutoReview\Makefile;

use function array_filter;
use function array_map;
use function array_merge;
use function array_pop;
use function array_values;
use function explode;
use function ltrim;
use const PHP_EOL;
use function preg_match;
use function strpos;
use function substr;
use function trim;

class Parser
{
    /**
     * @return array<string[]&string[][]>
     */
    public static function parse(string $makeFileContents): array
    {
        $targets = [];

        $multiline = false;
        $target = null;

        foreach (explode(PHP_EOL, $makeFileContents) as $line) {
            if (0 === strpos($line, "\t")
                || preg_match('/^\S+=.+$/u', $line)) {
                continue;
            }

            $line = trim($line);

            $previousMultiline = $multiline;

            if (false !== strpos($line, ':=')
                || 0 === strpos($line, '#')
            ) {
                continue;
            }

            $multiline = '\\' === substr($line, -1);

            if (false === $previousMultiline) {
                $targetParts = explode(':', $line);

                if (\count($targetParts) !== 2) {
                    continue;
                }

                $target = $targetParts[0];

                $dependencies = self::parseDependencies($targetParts[1], $multiline);
            } else {
                $lastEntry = array_pop($targets);

                $target = $lastEntry[0];

                $dependencies = array_merge(
                    $lastEntry[1],
                    self::parseDependencies($line, $multiline)
                );
            }

            $targets[] = [$target, $dependencies];
        }

        return $targets;
    }

    /**
     * @return string[]
     */
    private static function parseDependencies(string $dependencies, bool $multiline): array
    {
        if (false !== strpos($dependencies, '##')) {
            return [trim($dependencies)];
        }

        return array_values(
            array_filter(
                array_map(
                    static function (string $dependency) use ($multiline): string {
                        return trim(
                            $multiline ? ltrim($dependency, '\\') : $dependency
                        );
                    },
                    explode(' ', $dependencies)
                )
            )
        );
    }
}
