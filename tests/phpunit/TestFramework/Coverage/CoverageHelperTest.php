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

use Generator;
use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\MethodLocationData;
use PHPUnit\Framework\TestCase;

final class CoverageHelperTest extends TestCase
{
    /**
     * @dataProvider coverageProvider
     */
    public function test_it_can_convert_a_coverage_into_array(array $coverage, array $expected): void
    {
        $actual = CoverageHelper::convertToArray($coverage);

        $this->assertSame($expected, $actual);
    }

    public function coverageProvider(): Generator
    {
        yield 'empty' => [[], []];

        yield 'empty coverage file data' => [
            [
                '/path/to/file' => new CoverageFileData(),
            ],
            [
                '/path/to/file' => [
                    'byLine' => [],
                    'byMethod' => [],
                ],
            ],
        ];

        yield 'coverage file data with byLine data' => [
            [
                '/path/to/acme/Foo.php' => new CoverageFileData(
                    [
                        11 => [
                            CoverageLineData::with(
                                'Acme\FooTest::test_it_can_be_instantiated',
                                '/path/to/acme/FooTest.php',
                                0.000234
                            ),
                        ],
                    ]
                ),
            ],
            [
                '/path/to/acme/Foo.php' => [
                    'byLine' => [
                        11 => [
                            [
                                'testMethod' => 'Acme\FooTest::test_it_can_be_instantiated',
                                'testFilePath' => '/path/to/acme/FooTest.php',
                                'time' => 0.000234,
                            ],
                        ],
                    ],
                    'byMethod' => [],
                ],
            ],
        ];

        yield 'coverage coverage file data with byMethod data' => [
            [
                '/path/to/acme/Foo.php' => new CoverageFileData(
                    [],
                    [
                        '__construct' => new MethodLocationData(
                            19,
                            22
                        ),
                    ]
                ),
            ],
            [
                '/path/to/acme/Foo.php' => [
                    'byLine' => [],
                    'byMethod' => [
                        '__construct' => [
                            'startLine' => 19,
                            'endLine' => 22,
                        ],
                    ],
                ],
            ],
        ];

        yield 'nominal' => [
            [
                '/path/to/acme/Foo.php' => new CoverageFileData(
                    [
                        11 => [
                            CoverageLineData::with(
                                'Acme\FooTest::test_it_can_be_instantiated',
                                '/path/to/acme/FooTest.php',
                                0.000234
                            ),
                        ],
                    ],
                    [
                        '__construct' => new MethodLocationData(
                            19,
                            22
                        ),
                    ]
                ),
            ],
            [
                '/path/to/acme/Foo.php' => [
                    'byLine' => [
                        11 => [
                            [
                                'testMethod' => 'Acme\FooTest::test_it_can_be_instantiated',
                                'testFilePath' => '/path/to/acme/FooTest.php',
                                'time' => 0.000234,
                            ],
                        ],
                    ],
                    'byMethod' => [
                        '__construct' => [
                            'startLine' => 19,
                            'endLine' => 22,
                        ],
                    ],
                ],
            ],
        ];
    }
}
