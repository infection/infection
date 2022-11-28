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

use function array_column;
use function array_filter;
use function array_key_exists;
use function array_shift;
use function array_unshift;
use function current;
use function implode;
use PHPUnit\Framework\TestCase;
use function Safe\array_replace;
use Safe\Exceptions\ExecException;
use function Safe\file_get_contents;
use function Safe\shell_exec;
use function Safe\sprintf;
use function Safe\substr;
use function strpos;
use function substr_count;

/**
 * @coversNothing
 *
 * @group integration
 */
final class MakefileTest extends TestCase
{
    private const MAKEFILE_PATH = __DIR__ . '/../../../../Makefile';

    public function test_the_default_goal_is_the_help_command(): void
    {
        try {
            shell_exec('command -v timeout');
            $timeout = 'timeout 2s';
        } catch (ExecException) {
            $timeout = '';
        }
        $output = shell_exec(sprintf(
            '%s make --silent --file %s 2>&1',
            $timeout,
            self::MAKEFILE_PATH
        ));

        $expectedOutput = <<<'EOF'
[33mUsage:[0m
  make TARGET

[32m#
# Commands
#---------------------------------------------------------------------------[0m

[33mcompile:[0m	 	 Bundles Infection into a PHAR
[33mcompile-docker:[0m	 	 Bundles Infection into a PHAR using docker
[33mcs:[0m	  	 	 Runs PHP-CS-Fixer
[33mcs-check:[0m		 Runs PHP-CS-Fixer in dry-run mode
[33mprofile:[0m 	 	 Runs Blackfire
[33mautoreview:[0m 	 	 Runs various checks (static analysis & AutoReview test suite)
[33mtest:[0m		 	 Runs all the tests
[33mtest-docker:[0m		 Runs all the tests on the different Docker platforms
[33mtest-unit:[0m	 	 Runs the unit tests
[33mtest-unit-parallel:[0m	 Runs the unit tests in parallel
[33mtest-unit-docker:[0m	 Runs the unit tests on the different Docker platforms
[33mtest-e2e:[0m 	 	 Runs the end-to-end tests
[33mtest-e2e-phpunit:[0m	 Runs PHPUnit-enabled subset of end-to-end tests
[33mtest-e2e-docker:[0m 	 Runs the end-to-end tests on the different Docker platforms
[33mtest-infection:[0m		 Runs Infection against itself
[33mtest-infection-docker:[0m	 Runs Infection against itself on the different Docker platforms

EOF;

        $this->assertSame($expectedOutput, $output);
    }

    public function test_the_makefile_can_be_parsed(): void
    {
        Parser::parse(file_get_contents(self::MAKEFILE_PATH));

        $this->assertTrue(true);
    }

    public function test_phony_targets_are_correctly_declared(): void
    {
        $rules = Parser::parse(file_get_contents(self::MAKEFILE_PATH));

        $phony = null;
        $targetComment = false;
        $matchedPhony = true;

        foreach ($rules as [$target, $prerequisites]) {
            if ($target === '.PHONY') {
                $this->assertCount(
                    1,
                    $prerequisites,
                    sprintf(
                        'Expected one target to be declared as .PHONY. Found: "%s"',
                        implode('", "', $prerequisites)
                    )
                );

                $previousPhony = $phony;
                $phony = current($prerequisites);

                $this->assertTrue(
                    $matchedPhony,
                    sprintf(
                        '"%s" has been declared as a .PHONY target but no such target could '
                        . 'be found',
                        $previousPhony
                    )
                );

                $targetComment = false;
                $matchedPhony = false;

                continue;
            }

            if ($prerequisites !== [] && strpos($prerequisites[0], '#') === 0) {
                $this->assertStringStartsWith(
                    '## ',
                    $prerequisites[0],
                    'Expected the target comment to be a documented comment'
                );

                $this->assertSame(
                    $phony,
                    $target,
                    'Expected the declared target to match the previous declared .PHONY'
                );

                $this->assertFalse(
                    $targetComment,
                    sprintf(
                        'Did not expect to find twice the target comment line for "%s"',
                        $target
                    )
                );

                $this->assertFalse(
                    $matchedPhony,
                    sprintf(
                        'Did not expect to find the target comment line before its target '
                        . 'definition for "%s"',
                        $target
                    )
                );

                $targetComment = true;

                continue;
            }

            if ($phony !== null && $matchedPhony === false) {
                $matchedPhony = true;

                $this->assertSame(
                    $phony,
                    $target,
                    'Expected the declared target to match the previous declared .PHONY'
                );

                continue;
            }

            $phony = null;
            $targetComment = false;
            $matchedPhony = false;
        }
    }

    public function test_no_target_is_being_declared_twice(): void
    {
        $rules = Parser::parse(file_get_contents(self::MAKEFILE_PATH));

        $targetCounts = [];

        foreach ($rules as [$target, $prerequisites]) {
            if ($target === '.PHONY') {
                continue;
            }

            if ($prerequisites !== [] && strpos($prerequisites[0], '## ') === 0) {
                continue;
            }

            if (array_key_exists($target, $targetCounts)) {
                ++$targetCounts[$target];
            } else {
                $targetCounts[$target] = 1;
            }
        }

        foreach ($targetCounts as $target => $count) {
            $this->assertSame(
                1,
                $count,
                sprintf('Expected to find only one declaration for the target "%s"', $target)
            );
        }
    }

    public function test_all_docker_test_targets_are_properly_declared(): void
    {
        $testRules = array_filter(
            Parser::parse(file_get_contents(self::MAKEFILE_PATH)),
            static function (array $targetSet): bool {
                [$target, $prerequisites] = $targetSet;

                return strpos($target, 'test-') === 0
                    && substr($target, -7) === '-docker'
                    && ($prerequisites === []
                        || strpos($prerequisites[0], '## ') !== 0
                    )
                ;
            }
        );

        foreach ($testRules as [$target, $prerequisites]) {
            $dashCount = substr_count($target, '-') - 1;

            $subTestTargets = array_column(
                array_filter(
                    $testRules,
                    static function (array $rule) use ($target, $dashCount): bool {
                        $targetWithoutSuffix = substr($target, 0, -7);

                        $subTarget = substr($rule[0], 0, -7);

                        return strpos($subTarget, $targetWithoutSuffix . '-') === 0
                            && substr_count($subTarget, '-') === $dashCount + 1
                        ;
                    }
                ),
                0
            );

            if ($subTestTargets === []) {
                continue;
            }

            if ($target === 'test-docker') {
                array_unshift($subTestTargets, 'autoreview');
            }

            $this->assertSame(
                $subTestTargets,
                $prerequisites,
                sprintf(
                    'Expected the pre-requisite of the "%s" target to be "%s". Found "%s" instead',
                    $target,
                    implode(' ', $subTestTargets),
                    implode(' ', $prerequisites)
                )
            );
        }
    }

    public function test_the_test_target_runs_all_the_tests(): void
    {
        $testTargets = array_filter(
            Parser::parse(file_get_contents(self::MAKEFILE_PATH)),
            static function (array $rule): bool {
                [$target, $prerequisites] = $rule;

                return strpos($target, 'test') === 0
                    && strpos($target, 'tests/') !== 0
                    && substr($target, -7) !== '-docker'
                    && ($prerequisites === []
                        || strpos($prerequisites[0], '## ') !== 0
                    )
                ;
            }
        );

        // Exclude itself
        $testPrerequisites = array_shift($testTargets)[1];

        $rootTestTargets = array_column(
            array_filter(
                $testTargets,
                static function (array $rule): bool {
                    return strpos($rule[0], 'test-') === 0
                        && substr_count($rule[0], '-') === 1;
                }
            ),
            0
        );

        $rootTestTargets = array_replace($rootTestTargets, ['test-autoreview'], ['autoreview']);

        $this->assertSame($rootTestTargets, $testPrerequisites);
    }

    public function test_the_docker_test_target_runs_all_the_tests(): void
    {
        $testTargets = array_filter(
            Parser::parse(file_get_contents(self::MAKEFILE_PATH)),
            static function (array $rule): bool {
                [$target, $prerequisites] = $rule;

                return strpos($target, 'test') === 0
                    && substr($target, -7) === '-docker'
                    && ($prerequisites === []
                        || strpos($prerequisites[0], '## ') !== 0
                    )
                ;
            }
        );

        $testPrerequisites = array_shift($testTargets)[1];

        $rootTestTargets = array_column(
            array_filter(
                $testTargets,
                static function (array $rule): bool {
                    return strpos($rule[0], 'test-') === 0
                        && substr_count($rule[0], '-') === 2;
                }
            ),
            0
        );

        array_unshift($rootTestTargets, 'autoreview');

        $this->assertSame($rootTestTargets, $testPrerequisites);
    }
}
