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

namespace Infection\Tests\TestFramework\Tracing\Tracer;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\CannotBeInstantiated;
use Infection\TestFramework\Tracing\Trace\SourceMethodLineRange;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\Tests\TestFramework\Tracing\Trace\SyntheticTrace;
use Symfony\Component\Filesystem\Path;

final class PhpSpecProvider
{
    use CannotBeInstantiated;

    private const FIXTURES_DIR = __DIR__ . '/../../Coverage/Fixtures';

    public static function infoProvider(): iterable
    {
        $coverageDirectory = Path::canonicalize(self::FIXTURES_DIR . '/phpspec/');

        $indexXmlPath = Path::canonicalize($coverageDirectory . '/index.xml');
        $junitXmlPath = false;

        yield 'covered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/Calculator.php';
            $testPath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/spec/Covered/CalculatorSpec.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: true,
                    tests: new TestLocations(
                        byLine: [
                            9 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_adds_two_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_adds_negative_and_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_adds_two_negative_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_subtracts_two_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_subtracts_with_negative_result',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_subtracts_equal_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_multiplies_two_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_multiplies_negative_and_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_multiplies_by_zero',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_two_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_negative_and_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_equal_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_throws_exception_when_dividing_by_zero',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_throws_exception_when_dividing_by_zero',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            28 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_two_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_negative_and_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_equal_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            33 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_checks_if_positive_number_is_positive',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_checks_if_negative_number_is_not_positive',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_checks_if_zero_is_positive',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            38 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_returns_absolute_value_of_positive_number',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_returns_absolute_value_of_negative_number',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_returns_absolute_value_of_zero',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_handles_boundary_values_for_absolute',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_ensures_zero_is_not_negated_in_absolute',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                        ],
                        byMethod: [
                            'add' => new SourceMethodLineRange(7, 10),
                            'subtract' => new SourceMethodLineRange(12, 15),
                            'multiply' => new SourceMethodLineRange(17, 20),
                            'divide' => new SourceMethodLineRange(22, 29),
                            'isPositive' => new SourceMethodLineRange(31, 34),
                            'absolute' => new SourceMethodLineRange(36, 39),
                        ],
                    ),
                ),
            ];
        })();

        yield 'covered trait' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/LoggerTrait.php';
            $testPath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/spec/Covered/UserServiceSpec.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: true,
                    tests: new TestLocations(
                        byLine: [
                            11 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_public_log_method',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_public_log_method',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            21 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            26 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                        ],
                        byMethod: [
                            'log' => new SourceMethodLineRange(9, 12),
                            'getLogs' => new SourceMethodLineRange(14, 17),
                            'clearLogs' => new SourceMethodLineRange(19, 22),
                            'hasLogs' => new SourceMethodLineRange(24, 27),
                        ],
                    ),
                ),
            ];
        })();

        yield 'covered class with trait' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/UserService.php';
            $testPath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/spec/Covered/UserServiceSpec.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: true,
                    tests: new TestLocations(
                        byLine: [
                            13 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            18 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            20 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            23 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            30 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            31 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            32 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            35 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            36 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            37 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            42 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_null_for_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            47 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            52 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                        ],
                        byMethod: [
                            'addUser' => new SourceMethodLineRange(11, 26),
                            'removeUser' => new SourceMethodLineRange(28, 38),
                            'getUser' => new SourceMethodLineRange(40, 43),
                            'userExists' => new SourceMethodLineRange(45, 48),
                            'getUserCount' => new SourceMethodLineRange(50, 53),
                        ],
                    ),
                ),
            ];
        })();

        yield 'covered function' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/functions.php';
            $testPath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/spec/Covered/FormatNameFunctionSpec.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: true,
                    tests: new TestLocations(
                        byLine: [
                            7 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_last_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_no_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            8 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_no_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            11 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_last_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            12 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_last_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                        ],
                        byMethod: [],
                    ),
                ),
            ];
        })();
    }
}
