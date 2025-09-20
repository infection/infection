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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

#[CoversClass(ArgumentsAndOptionsBuilder::class)]
final class ArgumentsAndOptionsBuilderTest extends TestCase
{
    public function test_it_can_build_the_command_without_extra_options(): void
    {
        $builder = new ArgumentsAndOptionsBuilder(false, [], null);
        $configPath = '/config/path';

        $this->assertSame(
            [
                '--configuration',
                $configPath,
            ],
            $builder->buildForInitialTestsRun($configPath, ''),
        );
    }

    public function test_it_can_build_the_command_with_extra_options(): void
    {
        $builder = new ArgumentsAndOptionsBuilder(false, [], null);
        $configPath = '/config/path';

        $this->assertSame(
            [
                '--configuration',
                $configPath,
                '--verbose',
                '--debug',
            ],
            $builder->buildForInitialTestsRun($configPath, '--verbose --debug'),
        );
    }

    public function test_it_can_build_the_command_with_filtered_files_for_initial_tests_run(): void
    {
        $builder = new ArgumentsAndOptionsBuilder(false,
            [
                new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                new SplFileInfo('src/bar/Baz.php', 'src/bar/Baz.php', 'src/bar/Baz.php'),
            ],
            'simple',
        );
        $configPath = '/config/path';

        $this->assertSame(
            [
                '--configuration',
                $configPath,
                '--verbose',
                '--debug',
                '--filter',
                'FooTest|BazTest',
            ],
            $builder->buildForInitialTestsRun($configPath, '--verbose --debug'),
        );
    }

    public function test_it_can_build_the_command_with_extra_options_that_contains_spaces(): void
    {
        $builder = new ArgumentsAndOptionsBuilder(false, [], null);
        $configPath = '/the config/path';

        $this->assertSame(
            [
                '--configuration',
                $configPath,
                '--path=/a path/with spaces',
            ],
            $builder->buildForInitialTestsRun($configPath, '--path=/a path/with spaces'),
        );
    }

    #[DataProvider('provideTestCases')]
    public function test_it_can_build_the_command_with_filter_option_for_covering_tests_for_mutant(bool $executeOnlyCoveringTestCases, array $testCases, string $phpUnitVersion, ?string $expectedFilterOptionValue): void
    {
        $configPath = '/the config/path';

        $builder = new ArgumentsAndOptionsBuilder($executeOnlyCoveringTestCases, [], null);

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
                    $testCases,
                ),
                $phpUnitVersion,
            ),
        );
    }

    public static function provideTestCases(): Generator
    {
        $phpunit9 = '9.5';
        $phpunit10 = '10.1';

        yield '--only-covering-test-cases is disabled' => [
            false,
            [
                'App\ServiceTest::test_case1',
            ],
            $phpunit9,
            null,
        ];

        yield 'single test' => [
            true,
            [
                'App\ServiceTest::test_case1',
            ],
            $phpunit9,
            '/ServiceTest\:\:test_case1/',
        ];

        yield 'multiple tests of the same test case' => [
            true,
            [
                'App\ServiceTest::test_case1',
                'App\ServiceTest::test_case2',
            ],
            $phpunit9,
            '/ServiceTest\:\:test_case1|ServiceTest\:\:test_case2/',
        ];

        yield 'multiple tests with multiple test cases' => [
            true,
            [
                'App\ServiceUnitTest::test_case1',
                'App\ServiceUnitTest::test_case2',
                'App\ServiceIntegrationTest::test_case1',
                'App\ServiceIntegrationTest::test_case2',
            ],
            $phpunit9,
            '/ServiceUnitTest\:\:test_case1|ServiceUnitTest\:\:test_case2|ServiceIntegrationTest\:\:test_case1|ServiceIntegrationTest\:\:test_case2/',
        ];

        yield 'single test from a data provider item (<=PHPUnit9)' => [
            true,
            [
                'App\ServiceTest::test_case1 with data set "#1"',
            ],
            $phpunit9,
            '/ServiceTest\:\:test_case1 with data set "\#1"/',
        ];

        yield 'single test from a data provider item (>=PHPUnit10)' => [
            true,
            [
                'App\ServiceTest::test_case1##1',
            ],
            $phpunit10,
            '/ServiceTest\:\:test_case1 with data set "\#1"/',
        ];

        yield 'multiple tests from the same data provider (<=PHPUnit9)' => [
            true,
            [
                'App\ServiceTest::test_case with data set "#1"',
                'App\ServiceTest::test_case with data set "#2"',
            ],
            $phpunit9,
            '/ServiceTest\:\:test_case with data set "\#1"|ServiceTest\:\:test_case with data set "\#2"/',
        ];

        yield 'multiple tests from the same data provider (>=PHPUnit10)' => [
            true,
            [
                'App\ServiceTest::test_case##1',
                'App\ServiceTest::test_case##2',
            ],
            $phpunit10,
            '/ServiceTest\:\:test_case with data set "\#1"|ServiceTest\:\:test_case with data set "\#2"/',
        ];

        yield 'multiple tests from a data provider of the same test case (<=PHPUnit9)' => [
            true,
            [
                'App\ServiceTest::test_case1 with data set "#1"',
                'App\ServiceTest::test_case2 with data set "#1"',
            ],
            $phpunit9,
            '/ServiceTest\:\:test_case1 with data set "\#1"|ServiceTest\:\:test_case2 with data set "\#1"/',
        ];

        yield 'multiple tests from a data provider of the same test case (>=PHPUnit10)' => [
            true,
            [
                'App\ServiceTest::test_case1##1',
                'App\ServiceTest::test_case2##1',
            ],
            $phpunit10,
            '/ServiceTest\:\:test_case1 with data set "\#1"|ServiceTest\:\:test_case2 with data set "\#1"/',
        ];

        yield 'multiple tests with multiple test cases and multiple data provider items (<=PHPUnit9)' => [
            true,
            [
                'App\ServiceUnitTest::test_case1 with data set "#1"',
                'App\ServiceUnitTest::test_case1 with data set "#2"',
                'App\ServiceUnitTest::test_case2',
                'App\ServiceUnitTest::test_case3',
                'App\ServiceIntegrationTest::test_case1',
                'App\ServiceIntegrationTest::test_case2',
                'App\ServiceIntegrationTest::test_case3 with data set "#1"',
                'App\ServiceIntegrationTest::test_case3 with data set "#2"',
            ],
            $phpunit9,
            '/ServiceUnitTest\:\:test_case1 with data set "\#1"|ServiceUnitTest\:\:test_case1 with data set "\#2"|ServiceUnitTest\:\:test_case2|ServiceUnitTest\:\:test_case3|ServiceIntegrationTest\:\:test_case1|ServiceIntegrationTest\:\:test_case2|ServiceIntegrationTest\:\:test_case3 with data set "\#1"|ServiceIntegrationTest\:\:test_case3 with data set "\#2"/',
        ];

        yield 'multiple tests with multiple test cases and multiple data provider items (>=PHPUnit10)' => [
            true,
            [
                'App\ServiceUnitTest::test_case1##1',
                'App\ServiceUnitTest::test_case1##2',
                'App\ServiceUnitTest::test_case2',
                'App\ServiceUnitTest::test_case3',
                'App\ServiceIntegrationTest::test_case1',
                'App\ServiceIntegrationTest::test_case2',
                'App\ServiceIntegrationTest::test_case3##1',
                'App\ServiceIntegrationTest::test_case3##2',
            ],
            $phpunit10,
            '/ServiceUnitTest\:\:test_case1 with data set "\#1"|ServiceUnitTest\:\:test_case1 with data set "\#2"|ServiceUnitTest\:\:test_case2|ServiceUnitTest\:\:test_case3|ServiceIntegrationTest\:\:test_case1|ServiceIntegrationTest\:\:test_case2|ServiceIntegrationTest\:\:test_case3 with data set "\#1"|ServiceIntegrationTest\:\:test_case3 with data set "\#2"/',
        ];

        yield 'test from a data provider with a special character (<=PHPUnit9)' => [
            true,
            [
                'App\ServiceTest::test_case1 with data set "With special character >@&"\\::',
                'App\ServiceTest::test_case2',
            ],
            $phpunit9,
            '/ServiceTest\:\:test_case1 with data set "With special character \\>\\@\\&\\\\\:\:"|ServiceTest\:\:test_case2/',
        ];

        yield 'test from a data provider with a special character (>=PHPUnit10)' => [
            true,
            [
                'App\ServiceTest::test_case1 with data set "With special character >@&"\\::',
                'App\ServiceTest::test_case2',
            ],
            $phpunit10,
            '/ServiceTest\:\:test_case1 with data set "With special character \\>\\@\\&\\\\\:\:"|ServiceTest\:\:test_case2/',
        ];
    }
}
