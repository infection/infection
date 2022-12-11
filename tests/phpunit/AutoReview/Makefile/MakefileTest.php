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
use function array_values;
use function count;
use function current;
use function error_clear_last;
use function function_exists;
use function implode;
use PHPUnit\Framework\TestCase;
use function Safe\array_replace;
use Safe\Exceptions\ExecException;
use function Safe\file_get_contents;
use function Safe\shell_exec;
use function Safe\sprintf;
use function Safe\substr;
use function shell_exec as unsafe_shell_exec;
use function str_starts_with;
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

    public function test_it_has_a_help_command(): void
    {
        $expected = <<<'EOF'
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

        $actual = self::executeMakeCommand('help');

        $this->assertSame($expected, $actual);
    }

    public function test_the_default_goal_is_the_help_command(): void
    {
        $expected = self::executeMakeCommand('help');
        $actual = self::executeMakeCommand('');

        $this->assertSame($expected, $actual);
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

    public function test_it_declares_test_rules(): void
    {
        $testRuleTargets = array_column(
            self::getTestRules(false),
            0,
        );

        $this->assertArrayContains(
            $testRuleTargets,
            ['test', 'test-autoreview', 'test-unit', 'test-e2e']
        );
        $this->assertDoesNotArrayContain(
            $testRuleTargets,
            ['test-docker', 'test-unit-docker', 'test-e2e-docker']
        );
    }

    public function test_it_declares_docker_test_rules(): void
    {
        $testRuleTargets = array_column(
            self::getTestRules(true),
            0,
        );

        $this->assertArrayContains(
            $testRuleTargets,
            ['test-docker', 'test-unit-docker', 'test-e2e-docker']
        );
        $this->assertDoesNotArrayContain(
            $testRuleTargets,
            ['test', 'test-autoreview', 'test-unit', 'test-e2e']
        );
    }

    /**
     * @dataProvider subTargetProvider
     *
     * @param list<string> $expected
     * @param list<string> $notExpected
     */
    public function test_it_can_get_a_docker_test_target_sub_test_targets(
        string $target,
        array $expected,
        array $notExpected
    ): void {
        $subTestTargets = self::getDockerSubTestTargets(
            $target,
            self::getTestRules(true),
        );

        $this->assertArrayContains(
            $subTestTargets,
            $expected,
        );
        $this->assertDoesNotArrayContain(
            $subTestTargets,
            $notExpected,
        );
    }

    public static function subTargetProvider(): iterable
    {
        yield [
            'test-docker',
            ['test-unit-docker', 'test-e2e-docker'],
            ['test-docker', 'test-unit', 'test-e2e'],
        ];

        yield [
            'test-unit-docker',
            ['test-unit-80-docker'],
            ['test-unit-docker', 'test-unit-80', 'test-unit'],
        ];
    }

    /**
     * @dataProvider rootTestTargetProvider
     *
     * @param list<string> $expected
     * @param list<string> $notExpected
     */
    public function test_it_can_get_all_the_root_test_targets(
        bool $docker,
        array $expected,
        array $notExpected
    ): void {
        $dashCount = $docker ? 2 : 1;

        $rootTestRules = self::getTestRules($docker);
        array_shift($rootTestRules);

        $rootTestTargets = self::getRootTestTargets(
            $rootTestRules,
            $dashCount,
        );

        $this->assertArrayContains(
            $rootTestTargets,
            $expected,
        );
        $this->assertDoesNotArrayContain(
            $rootTestTargets,
            $notExpected,
        );
    }

    public static function rootTestTargetProvider(): iterable
    {
        yield [
            true,
            ['test-unit-docker', 'test-e2e-docker'],
            ['test-docker', 'test-unit', 'test-e2e'],
        ];

        yield [
            false,
            ['test-unit', 'test-e2e'],
            ['test', 'test-unit-docker', 'test-e2e-docker'],
        ];
    }

    public function test_all_docker_test_targets_are_properly_declared(): void
    {
        $testRules = self::getTestRules(true);

        foreach ($testRules as [$target, $prerequisites]) {
            $subTestTargets = self::getDockerSubTestTargets($target, $testRules);

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
        $testRules = self::getTestRules(false);

        // Exclude itself
        $testPrerequisites = array_shift($testRules)[1];

        $rootTestTargets = self::getRootTestTargets($testRules, 1);
        $rootTestTargets = array_replace($rootTestTargets, ['test-autoreview'], ['autoreview']);

        $this->assertEqualsCanonicalizing($rootTestTargets, $testPrerequisites);
    }

    public function test_the_docker_test_target_runs_all_the_tests(): void
    {
        $testRules = self::getTestRules(true);

        // Exclude itself
        $testPrerequisites = array_shift($testRules)[1];

        $rootTestTargets = self::getRootTestTargets($testRules, 2);
        array_unshift($rootTestTargets, 'autoreview');

        $this->assertEqualsCanonicalizing($rootTestTargets, $testPrerequisites);
    }

    private static function executeMakeCommand(string $commandName): string
    {
        $timeout = self::getTimeout();

        return self::executeCommand(
            sprintf(
                '%s make %s help --silent --file %s 2>&1',
                $timeout,
                $commandName,
                self::MAKEFILE_PATH,
            ),
        );
    }

    // TODO: remove this as we remove support for PHP 7.4 and Safe v1
    private static function executeCommand(string $command): string
    {
        if (function_exists('Safe\shell_exec')) {
            return shell_exec($command);
        }

        error_clear_last();

        $safeResult = unsafe_shell_exec($command);

        if ($safeResult === null || $safeResult === false) {
            throw ExecException::createFromPhpError();
        }

        return $safeResult;
    }

    private static function getTimeout(): string
    {
        try {
            self::executeCommand('command -v timeout');

            return 'timeout 2s';
        } catch (ExecException $execException) {
            return '';
        }
    }

    /**
     * @return list<array{string, list<string>}>
     */
    private static function getTestRules(bool $dockerTargets): array
    {
        $filterDockerTarget = $dockerTargets
            ? static fn (string $target) => substr($target, -7) === '-docker'
            : static fn (string $target) => substr($target, -7) !== '-docker';

        return array_values(
            array_filter(
                Parser::parse(file_get_contents(self::MAKEFILE_PATH)),
                static function (array $rule) use ($filterDockerTarget): bool {
                    [$target, $prerequisites] = $rule;

                    $isCommentRule = count($prerequisites) !== 0 && str_starts_with($prerequisites[0], '## ');

                    return str_starts_with($target, 'test')
                        && !str_starts_with($target, 'tests/')
                        && $filterDockerTarget($target)
                        && !$isCommentRule;
                }
            ),
        );
    }

    /**
     * @param list<array{string, list<string>}> $testRules
     *
     * @return list<string>
     */
    private static function getDockerSubTestTargets(string $target, array $testRules): array
    {
        $dashCount = substr_count($target, '-') - 1;

        $subTestRules = array_filter(
            $testRules,
            static function (array $rule) use ($target, $dashCount): bool {
                $targetWithoutSuffix = substr($target, 0, -7);
                $subTarget = substr($rule[0], 0, -7);

                return str_starts_with($subTarget, $targetWithoutSuffix . '-')
                    && substr_count($subTarget, '-') === $dashCount + 1;
            }
        );

        return array_column($subTestRules, 0);
    }

    /**
     * @param list<array{string, list<string>}> $testRules
     *
     * @return list<string>
     */
    private static function getRootTestTargets(array $testRules, int $dashCount): array
    {
        $testTargets = array_column($testRules, 0);

        return array_values(
            array_filter(
                $testTargets,
                static fn (string $target): bool => str_starts_with($target, 'test-')
                    && substr_count($target, '-') === $dashCount,
            ),
        );
    }

    /**
     * @template T
     *
     * @param T[] $array
     * @param T[] $items
     */
    private function assertArrayContains(
        array $array,
        array $items
    ): void {
        if (count($items) === 0) {
            $this->addToAssertionCount(1);

            return;
        }

        foreach ($items as $item) {
            $this->assertContains($item, $array);
        }
    }

    /**
     * @template T
     *
     * @param T[] $array
     * @param T[] $items
     */
    private function assertDoesNotArrayContain(
        array $array,
        array $items
    ): void {
        if (count($items) === 0) {
            $this->addToAssertionCount(1);

            return;
        }

        foreach ($items as $item) {
            $this->assertNotContains($item, $array);
        }
    }
}
