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

namespace Infection\Tests\TestFramework\PhpUnit\CommandLine;

use function array_map;
use Generator;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use PHPUnit\Framework\TestCase;

final class ArgumentsAndOptionsBuilderTest extends TestCase
{
    /**
     * @var ArgumentsAndOptionsBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new ArgumentsAndOptionsBuilder(false);
    }

    public function test_it_can_build_the_command_without_extra_options(): void
    {
        $configPath = '/config/path';

        $this->assertSame(
            [
                '--configuration',
                $configPath,
            ],
            $this->builder->buildForInitialTestsRun($configPath, '')
        );
    }

    public function test_it_can_build_the_command_with_extra_options(): void
    {
        $configPath = '/config/path';

        $this->assertSame(
            [
                '--configuration',
                $configPath,
                '--verbose',
                '--debug',
            ],
            $this->builder->buildForInitialTestsRun($configPath, '--verbose --debug')
        );
    }

    public function test_it_can_build_the_command_with_extra_options_that_contains_spaces(): void
    {
        $configPath = '/the config/path';

        $this->assertSame(
            [
                '--configuration',
                $configPath,
                '--path=/a path/with spaces',
            ],
            $this->builder->buildForInitialTestsRun($configPath, '--path=/a path/with spaces')
        );
    }

    /**
     * @dataProvider provideTestCases
     */
    public function test_it_can_build_the_command_with_filter_option_for_covering_tests_for_mutant(bool $executeOnlyCoveringTestCases, array $testCases, ?string $expectedFilterOptionValue = null): void
    {
        $configPath = '/the config/path';

        $builder = new ArgumentsAndOptionsBuilder($executeOnlyCoveringTestCases);

        $expectedArgumentsAndOptions = [
            '--configuration',
            $configPath,
            '--path=/a path/with spaces',
        ];

        if ($executeOnlyCoveringTestCases) {
            $expectedArgumentsAndOptions[] = '--filter';
            $expectedArgumentsAndOptions[] = $expectedFilterOptionValue;
        }

        $this->assertSame(
            $expectedArgumentsAndOptions,
            $builder->buildForMutant(
                $configPath,
                '--path=/a path/with spaces',
                array_map(
                    static fn (string $testCase): TestLocation => TestLocation::forTestMethod($testCase),
                    $testCases
                )
            )
        );
    }

    public function provideTestCases(): Generator
    {
        yield '--only-covering-test-cases is disabled' => [
            false,
            [
                'App\Test::test_case1',
            ],
        ];

        yield '1 test case' => [
            true,
            [
                'App\Test::test_case1',
            ],
            '/App\\\\Test\:\:test_case1/',
        ];

        yield '2 test cases' => [
            true,
            [
                'App\Test::test_case1',
                'App\Test::test_case2',
            ],
            '/App\\\\Test\:\:test_case1|App\\\\Test\:\:test_case2/',
        ];

        yield '2 simple test cases, 1 with data set and special character >' => [
            true,
            [
                'App\Test::test_case1 with data set "With special character >"',
                'App\Test::test_case2',
            ],
            '/App\\\\Test\:\:test_case1 with data set "With special character \\>"|App\\\\Test\:\:test_case2/',
        ];

        yield '2 simple test cases, 1 with data set and special character @' => [
            true,
            [
                'App\Test::test_case1 with data set "With special character @"',
                'App\Test::test_case2',
            ],
            '/App\\\\Test\:\:test_case1 with data set "With special character @"|App\\\\Test\:\:test_case2/',
        ];
    }
}
