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
use function array_shift;
use function chdir;
use function current;
use function file_get_contents;
use function getcwd;
use function implode;
use PHPUnit\Framework\TestCase;
use function shell_exec;
use function sprintf;
use function strpos;
use function substr_count;

/**
 * @internal
 *
 * @coversNothing
 *
 * @group infra
 */
final class MakefileTest extends TestCase
{
    private const MAKEFILE_PATH = __DIR__ . '/../../../Makefile';

    /**
     * @var string
     */
    private static $originalCwd;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        self::$originalCwd = getcwd();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        chdir(self::$originalCwd);
    }

    public function test_the_default_goal_is_the_help_command(): void
    {
        $output = shell_exec(sprintf('make -f %s', self::MAKEFILE_PATH));

        $expectedOutput = <<<'EOF'
[33mUsage:[0m
  make TARGET

[32m#
# Commands
#---------------------------------------------------------------------------[0m

[33mcompile:[0m	 Bundles Infection into a PHAR
[33mcs:[0m	  	 Runs PHP-CS-Fixer
[33mphpstan:[0m  	 Runs PHPStan
[33manalyze:[0m	 Runs Static analyzers and various other checks
[33mvalidate:[0m	 Checks that the composer.json file is valid
[33mtest:[0m		 Runs all the tests
[33mtest-unit:[0m	 Runs the unit tests
[33mtest-e2e:[0m 	 Runs the end-to-end tests
[33mtest-infection:[0m  Runs Infection against itself

EOF;

        $this->assertSame($expectedOutput, $output);
    }

    public function test_the_makefile_can_be_parsed(): void
    {
        (new Parser())->parse(file_get_contents(self::MAKEFILE_PATH));

        $this->assertTrue(true);
    }

    public function test_phony_targets_are_correctly_declared(): void
    {
        $targets = (new Parser())->parse(file_get_contents(self::MAKEFILE_PATH));

        $phony = null;
        $targetComment = false;
        $matchedPhony = true;

        foreach ($targets as [$target, $dependencies]) {
            if ('.PHONY' === $target) {
                $this->assertCount(
                    1,
                    $dependencies,
                    sprintf(
                        'Expected one target to be declared as .PHONY. Found: "%s"',
                        implode('", "', $dependencies)
                    )
                );

                $previousPhony = $phony;
                $phony = current($dependencies);

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

            if ([] !== $dependencies && strpos($dependencies[0], '#') === 0) {
                $this->assertStringStartsWith(
                    '## ',
                    $dependencies[0],
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

            if (null !== $phony && false === $matchedPhony) {
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
        $targets = (new Parser())->parse(file_get_contents(self::MAKEFILE_PATH));

        $targetCounts = [];

        foreach ($targets as [$target, $dependencies]) {
            if ('.PHONY' === $target) {
                continue;
            }

            if ([] !== $dependencies && strpos($dependencies[0], '## ') === 0) {
                continue;
            }

            if (\array_key_exists($target, $targetCounts)) {
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

    public function test_all_test_targets_are_properly_declared(): void
    {
        $testTargets = array_filter(
            (new Parser())->parse(file_get_contents(self::MAKEFILE_PATH)),
            static function (array $targetSet): bool {
                [$target, $dependencies] = $targetSet;

                return strpos($target, 'test-') === 0
                    && ([] === $dependencies
                        || strpos($dependencies[0], '## ') !== 0
                    )
                ;
            }
        );

        foreach ($testTargets as [$target, $dependencies]) {
            $dashCount = substr_count($target, '-');

            $subTestTargets = array_column(
                array_filter(
                    $testTargets,
                    static function (array $targetSet) use ($target, $dashCount): bool {
                        return strpos($targetSet[0], $target . '-') === 0
                            && substr_count($targetSet[0], '-') === $dashCount + 1;
                    }
                ),
                0
            );

            if ([] === $subTestTargets) {
                continue;
            }

            $this->assertSame($subTestTargets, $dependencies);
        }
    }

    public function test_the_test_target_runs_all_the_tests(): void
    {
        $testTargets = array_filter(
            (new Parser())->parse(file_get_contents(self::MAKEFILE_PATH)),
            static function (array $targetSet): bool {
                [$target, $dependencies] = $targetSet;

                return strpos($target, 'test') === 0
                    && ([] === $dependencies
                        || strpos($dependencies[0], '## ') !== 0
                    )
                ;
            }
        );

        $testDependencies = array_shift($testTargets)[1];

        $rootTestTargets = array_column(
            array_filter(
                $testTargets,
                static function (array $targetSet): bool {
                    return strpos($targetSet[0], 'test-') === 0
                        && substr_count($targetSet[0], '-') === 1;
                }
            ),
            0
        );

        $this->assertSame($rootTestTargets, $testDependencies);
    }
}
