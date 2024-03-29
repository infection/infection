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
use Infection\TestFramework\Coverage\SourceMethodLineRange;
use Infection\TestFramework\Coverage\TestLocations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestLocations::class)]
final class TestLocationsTest extends TestCase
{
    public function test_it_has_default_values(): void
    {
        $testLocations = new TestLocations();

        $this->assertSame([], $testLocations->getTestsLocationsBySourceLine());
        $this->assertSame([], $testLocations->getSourceMethodRangeByMethod());
    }

    public function test_it_can_be_instantiated(): void
    {
        $testLocations = new TestLocations(
            [
                22 => [
                    new TestLocation(
                        '\A\B\C::test_it_works',
                        '/path/to/A/B/C.php',
                        0.34325,
                    ),
                ],
            ],
            [
                'mutate' => new SourceMethodLineRange(12, 16),
                'createNode' => new SourceMethodLineRange(32, 33),
            ],
        );

        $this->assertSame(
            [
                [
                    'byLine' => [
                        22 => [
                            [
                                'testMethod' => '\A\B\C::test_it_works',
                                'testFilePath' => '/path/to/A/B/C.php',
                                'testExecutionTime' => 0.34325,
                            ],
                        ],
                    ],
                    'byMethod' => [
                        'mutate' => [
                            'startLine' => 12,
                            'endLine' => 16,
                        ],
                        'createNode' => [
                            'startLine' => 32,
                            'endLine' => 33,
                        ],
                    ],
                ],
            ],
            TestLocationsNormalizer::normalize([$testLocations]),
        );
    }

    public function test_it_can_expose_its_tests_locations_by_reference(): void
    {
        $testLocations = new TestLocations(
            [
                22 => [
                    new TestLocation(
                        '\A\B\C::test_it_works',
                        '/path/to/A/B/C.php',
                        0.34325,
                    ),
                ],
            ],
            [
                'mutate' => new SourceMethodLineRange(12, 16),
                'createNode' => new SourceMethodLineRange(32, 33),
            ],
        );

        foreach ($testLocations->getTestsLocationsBySourceLine() as $testsLocations) {
            foreach ($testsLocations as $line => $test) {
                $testsLocations[$line] = null;
            }
        }

        $this->assertSame(
            [
                [
                    'byLine' => [
                        22 => [
                            [
                                'testMethod' => '\A\B\C::test_it_works',
                                'testFilePath' => '/path/to/A/B/C.php',
                                'testExecutionTime' => 0.34325,
                            ],
                        ],
                    ],
                    'byMethod' => [
                        'mutate' => [
                            'startLine' => 12,
                            'endLine' => 16,
                        ],
                        'createNode' => [
                            'startLine' => 32,
                            'endLine' => 33,
                        ],
                    ],
                ],
            ],
            TestLocationsNormalizer::normalize([$testLocations]),
        );

        foreach ($testLocations->getTestsLocationsBySourceLine() as &$testsLocations) {
            foreach ($testsLocations as $line => $test) {
                $testsLocations[$line] = null;
            }
        }
        unset($testsLocations);

        $this->assertSame(
            [
                [
                    'byLine' => [
                        22 => [null],
                    ],
                    'byMethod' => [
                        'mutate' => [
                            'startLine' => 12,
                            'endLine' => 16,
                        ],
                        'createNode' => [
                            'startLine' => 32,
                            'endLine' => 33,
                        ],
                    ],
                ],
            ],
            TestLocationsNormalizer::normalize([$testLocations]),
        );
    }
}
