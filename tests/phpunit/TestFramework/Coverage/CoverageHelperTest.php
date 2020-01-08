<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Coverage;

use Generator;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\MethodLocationData;
use PHPUnit\Framework\TestCase;
use function is_array;
use function is_scalar;

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
                                'time' => 0.000234
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
                                'time' => 0.000234
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
