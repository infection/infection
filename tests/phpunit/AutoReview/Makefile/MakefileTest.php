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

use Fidry\Makefile\Rule;
use Fidry\Makefile\Test\BaseMakefileTestCase;
use function array_column;
use function array_filter;
use function array_key_exists;
use function array_map;
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
final class MakefileTest extends BaseMakefileTestCase
{
    private const MAKEFILE_PATH = __DIR__ . '/../../../../Makefile';

    protected static function getMakefilePath(): string
    {
        return self::MAKEFILE_PATH;
    }

    protected function getExpectedHelpOutput(): string
    {
        return <<<'EOF'
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
    }

    public function test_the_default_goal_is_the_help_command(): void
    {
        $expected = self::executeMakeCommand('help');
        $actual = self::executeMakeCommand('');

        $this->assertSame($expected, $actual);
    }

    public function test_all_docker_test_targets_are_properly_declared(): void
    {
        $testRules = self::getTestRules(true);

        foreach ($testRules as $rule) {
            $target = $rule->getTarget();
            $prerequisites = $rule->getPrerequisites();

            $subTestTargets = self::getSubTestRules($target, $testRules);

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
        $testPrerequisites = array_shift($testRules)->getPrerequisites();

        $rootTestTargets = self::getRootTestTargets($testRules, 1);
        $rootTestTargets = array_replace($rootTestTargets, ['test-autoreview'], ['autoreview']);

        $this->assertEqualsCanonicalizing($rootTestTargets, $testPrerequisites);
    }

    public function test_the_docker_test_target_runs_all_the_tests(): void
    {
        $testRules = self::getTestRules(true);

        $testPrerequisites = array_shift($testRules)->getPrerequisites();

        $rootTestTargets = self::getRootTestTargets($testRules, 2);
        array_unshift($rootTestTargets, 'autoreview');

        $this->assertEqualsCanonicalizing($rootTestTargets, $testPrerequisites);
    }

    /**
     * @return list<Rule>
     */
    private static function getTestRules(bool $dockerTargets): array
    {
        $filterDockerTarget = $dockerTargets
            ? static fn (string $target) => substr($target, -7) === '-docker'
            : static fn (string $target) => substr($target, -7) !== '-docker';

        return array_values(
            array_filter(
                self::getParsedRules(),
                static function (Rule $rule) use ($filterDockerTarget): bool {
                    $target = $rule->getTarget();

                    return str_starts_with($target, 'test')
                        && !str_starts_with($target, 'tests/')
                        && $filterDockerTarget($target)
                        && !$rule->isComment();
                }
            ),
        );
    }

    /**
     * @param list<Rule> $testRules
     *
     * @return list<string>
     */
    private static function getSubTestRules(string $target, array $testRules): array
    {
        $dashCount = substr_count($target, '-') - 1;

        $subTestRules = array_filter(
            $testRules,
            static function (Rule $rule) use ($target, $dashCount): bool {
                $targetWithoutSuffix = substr($target, 0, -7);
                $subTarget = substr($rule->getTarget(), 0, -7);

                return str_starts_with($subTarget, $targetWithoutSuffix . '-')
                    && substr_count($subTarget, '-') === $dashCount + 1;
            }
        );

        return array_column($subTestRules, 0);
    }

    /**
     * @param list<Rule> $testRules
     *
     * @return list<string>
     */
    private static function getRootTestTargets(array $testRules, int $dashCount): array
    {
        $testTargets = array_map(
            static fn (Rule $rule) => $rule->getTarget(),
            $testRules,
        );

        return array_values(
            array_filter(
                $testTargets,
                static fn (string $target): bool => str_starts_with($target, 'test-')
                    && substr_count($target, '-') === $dashCount,
            ),
        );
    }
}
