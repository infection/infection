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

namespace Infection\Tests\TestFramework\PhpUnit\Coverage;

final class XmlCoverageFixtures
{
    public const FIXTURES_SRC_DIR = __DIR__ . '/../../../Fixtures/Files/phpunit/coverage/src';
    public const FIXTURES_COVERAGE_DIR = __DIR__ . '/../../../Fixtures/Files/phpunit/coverage/coverage-xml';
    public const FIXTURES_INCORRECT_COVERAGE_DIR = __DIR__ . '/../../../Fixtures/Files/phpunit/coverage-incomplete';
    public const FIXTURES_OLD_COVERAGE_DIR = __DIR__ . '/../../../Fixtures/Files/phpunit/old-coverage/coverage-xml';
    public const FIXTURES_OLD_SRC_DIR = __DIR__ . '/../../../Fixtures/Files/phpunit/old-coverage/src';

    /**
     * @return iterable<XmlCoverageFixture>
     */
    public static function provideFixtures(): iterable
    {
        yield new XmlCoverageFixture(
            self::FIXTURES_COVERAGE_DIR,
            'FirstLevel/firstLevel.php.xml',
            self::FIXTURES_SRC_DIR,
            '/FirstLevel/firstLevel.php',
            [
                'byLine' => [
                    26 => [
                        [
                            'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                        [
                            'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                    ],
                    30 => [
                        [
                            'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                        [
                            'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                    ],
                    31 => [
                        [
                            'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                    ],
                    34 => [
                        [
                            'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                    ],
                ],
                'byMethod' => [
                    'mutate' => [
                        'startLine' => 19,
                        'endLine' => 22,
                    ],
                    'shouldMutate' => [
                        'startLine' => 24,
                        'endLine' => 35,
                    ],
                ],
            ]
        );

        yield new XmlCoverageFixture(
            self::FIXTURES_COVERAGE_DIR,
            'FirstLevel/SecondLevel/secondLevel.php.xml',
            self::FIXTURES_SRC_DIR,
            '/FirstLevel/SecondLevel/secondLevel.php',
            [
                'byLine' => [
                    11 => [
                        [
                            'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                        [
                            'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                    ],
                ],
                'byMethod' => [
                    'mutate' => [
                        'startLine' => 19,
                        'endLine' => 22,
                    ],
                    'shouldMutate' => [
                        'startLine' => 24,
                        'endLine' => 35,
                    ],
                ],
            ]
        );

        yield new XmlCoverageFixture(
            self::FIXTURES_COVERAGE_DIR,
            'FirstLevel/SecondLevel/secondLevelTrait.php.xml',
            self::FIXTURES_SRC_DIR,
            '/FirstLevel/SecondLevel/secondLevelTrait.php',
            [
                'byLine' => [
                    11 => [
                        [
                            'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                        [
                            'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                    ],
                ],
                'byMethod' => [
                    'mutate' => [
                        'startLine' => 19,
                        'endLine' => 22,
                    ],
                    'shouldMutate' => [
                        'startLine' => 24,
                        'endLine' => 35,
                    ],
                ],
            ]
        );

        yield new XmlCoverageFixture(
            self::FIXTURES_COVERAGE_DIR,
            'zeroLevel.php.xml',
            self::FIXTURES_SRC_DIR,
            '/zeroLevel.php',
            [
                'byLine' => [],
                'byMethod' => [],
            ]
        );

        yield new XmlCoverageFixture(
            self::FIXTURES_COVERAGE_DIR,
            'noPercentage.php.xml',
            self::FIXTURES_SRC_DIR,
            '/noPercentage.php',
            [
                'byLine' => [],
                'byMethod' => [],
            ]
        );
    }

    /**
     * @return iterable<XmlCoverageFixture>
     */
    public static function provideLegacyFormatFixtures(): iterable
    {
        yield new XmlCoverageFixture(
            self::FIXTURES_OLD_COVERAGE_DIR,
            'Middleware/ReleaseRecordedEventsMiddleware.php.xml',
            self::FIXTURES_OLD_SRC_DIR,
            '/Middleware/ReleaseRecordedEventsMiddleware.php',
            [
                'byLine' => [
                    29 => [
                        [
                            'testMethod' => 'BornFree\TacticianDomainEvent\Tests\Middleware\ReleaseRecordedEventsMiddlewareTest::it_dispatches_recorded_events',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                        [
                            'testMethod' => 'BornFree\TacticianDomainEvent\Tests\Middleware\ReleaseRecordedEventsMiddlewareTest::it_erases_events_when_exception_is_raised',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                    ],
                    30 => [
                        [
                            'testMethod' => 'BornFree\TacticianDomainEvent\Tests\Middleware\ReleaseRecordedEventsMiddlewareTest::it_dispatches_recorded_events',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                        [
                            'testMethod' => 'BornFree\TacticianDomainEvent\Tests\Middleware\ReleaseRecordedEventsMiddlewareTest::it_erases_events_when_exception_is_raised',
                            'testFilePath' => null,
                            'time' => null,
                        ],
                    ],
                ],
                'byMethod' => [
                    '__construct' => [
                        'startLine' => 27,
                        'endLine' => 31,
                    ],
                    'execute' => [
                        'startLine' => 43,
                        'endLine' => 60,
                    ],
                ],
            ]
        );
    }
}
