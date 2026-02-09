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

use function array_filter;
use function array_map;
use function array_shift;
use function array_unshift;
use function array_values;
use function count;
use Fidry\Makefile\Rule;
use Fidry\Makefile\Test\BaseMakefileTestCase;
use function implode;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\array_replace;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use function substr;
use function substr_count;

#[Group('integration')]
#[CoversNothing]
final class MakefileTest extends BaseMakefileTestCase
{
    private const MAKEFILE_PATH = __DIR__ . '/../../../../Makefile';

    public function test_the_default_goal_is_the_help_command(): void
    {
        $expected = self::executeMakeCommand('help');
        $actual = self::executeMakeCommand('');

        $this->assertSame($expected, $actual);
    }

    public function test_it_declares_test_rules(): void
    {
        $testRuleTargets = array_map(
            static fn (Rule $rule) => $rule->getTarget(),
            self::getTestRules(false),
        );

        $this->assertArrayContains(
            $testRuleTargets,
            ['test', 'test-autoreview', 'test-unit', 'test-e2e'],
        );
        $this->assertDoesNotArrayContain(
            $testRuleTargets,
            ['test-docker', 'test-unit-docker', 'test-e2e-docker'],
        );
    }

    public function test_it_declares_docker_test_rules(): void
    {
        $testRuleTargets = array_map(
            static fn (Rule $rule) => $rule->getTarget(),
            self::getTestRules(true),
        );

        $this->assertArrayContains(
            $testRuleTargets,
            ['test-docker', 'test-unit-docker', 'test-e2e-docker'],
        );
        $this->assertDoesNotArrayContain(
            $testRuleTargets,
            ['test', 'test-autoreview', 'test-unit', 'test-e2e'],
        );
    }

    /**
     * @param list<string> $expected
     * @param list<string> $notExpected
     */
    #[DataProvider('subTargetProvider')]
    public function test_it_can_get_a_docker_test_target_sub_test_targets(
        string $target,
        array $expected,
        array $notExpected,
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
            [],
            ['test-unit-docker', 'test-unit'],
        ];
    }

    /**
     * @param list<string> $expected
     * @param list<string> $notExpected
     */
    #[DataProvider('rootTestTargetProvider')]
    public function test_it_can_get_all_the_root_test_targets(
        bool $docker,
        array $expected,
        array $notExpected,
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

        foreach ($testRules as $rule) {
            $target = $rule->getTarget();
            $prerequisites = $rule->getPrerequisites();

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
                    implode(' ', $prerequisites),
                ),
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

        // Exclude itself
        $testPrerequisites = array_shift($testRules)->getPrerequisites();

        $rootTestTargets = self::getRootTestTargets($testRules, 2);
        array_unshift($rootTestTargets, 'autoreview');

        $this->assertEqualsCanonicalizing($rootTestTargets, $testPrerequisites);
    }

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
            [33mtest-benchmark:[0m	 	 Runs the benchmark tests
            [33mtest-e2e:[0m 	 	 Runs the end-to-end tests
            [33mtest-e2e-phpunit:[0m	 Runs PHPUnit-enabled subset of end-to-end tests
            [33mtest-e2e-docker:[0m 	 Runs the end-to-end tests on the different Docker platforms
            [33mtest-infection:[0m		 Runs Infection against itself
            [33mtest-infection-docker:[0m	 Runs Infection against itself on the different Docker platforms

            EOF;
    }

    /**
     * @return list<Rule>
     */
    private static function getTestRules(bool $dockerTargets): array
    {
        $filterDockerTarget = $dockerTargets
            ? static fn (string $target) => str_ends_with($target, '-docker')
            : static fn (string $target) => !str_ends_with($target, '-docker');

        return array_values(
            array_filter(
                self::getParsedRules(),
                static function (Rule $rule) use ($filterDockerTarget): bool {
                    $target = $rule->getTarget();

                    return str_starts_with($target, 'test')
                        && !str_starts_with($target, 'tests/')
                        && $filterDockerTarget($target)
                        && !$rule->isComment();
                },
            ),
        );
    }

    /**
     * @param list<Rule> $testRules
     *
     * @return list<string>
     */
    private static function getDockerSubTestTargets(string $target, array $testRules): array
    {
        $dashCount = substr_count($target, '-') - 1;

        $subTestRules = array_filter(
            $testRules,
            static function (Rule $rule) use ($target, $dashCount): bool {
                $targetWithoutSuffix = substr($target, 0, -7);
                $subTarget = substr($rule->getTarget(), 0, -7);

                return str_starts_with($subTarget, $targetWithoutSuffix . '-')
                    && substr_count($subTarget, '-') === $dashCount + 1;
            },
        );

        return array_values(
            array_map(
                static fn (Rule $rule) => $rule->getTarget(),
                $subTestRules,
            ),
        );
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

    /**
     * @template T
     *
     * @param T[] $array
     * @param T[] $items
     */
    private function assertArrayContains(
        array $array,
        array $items,
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
        array $items,
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
