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

namespace Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestMissingEnvironmentVariable;

use Infection\FileSystem\FileSystem;
use Infection\Testing\SingletonContainer;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestMissingEnvironmentVariable;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestMissingEnvironmentVariable\Fixtures\TestCoveringDynamicEnvironmentVariableTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestMissingEnvironmentVariable\Fixtures\TestDeclaringEnvironmentVariableTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestMissingEnvironmentVariable\Fixtures\TestDirectlyUsingEnvTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestMissingEnvironmentVariable\Fixtures\TestMissingEnvironmentVariableTest;
use Infection\Tests\Architecture\PHPat\Selector\SelectorTestCase;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\Analyser;
use Infection\Tests\Architecture\PHPat\Selector\Support\EnvironmentVariableUsageDetector;
use Infection\Tests\Console\ApplicationTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PHPUnitTestMissingEnvironmentVariable::class)]
#[CoversClass(EnvironmentVariableUsageDetector::class)]
final class PHPUnitTestMissingEnvironmentVariableTest extends SelectorTestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('classProvider')]
    public function test_it_matches_tests_missing_environment_variables(
        string $className,
        bool $expected,
    ): void {
        $selector = new PHPUnitTestMissingEnvironmentVariable(
            new EnvironmentVariableUsageDetector(
                new Analyser(
                    SingletonContainer::getContainer()->getParser(),
                    new FileSystem(),
                ),
                $this->getReflectionProvider(),
            ),
        );

        $actual = $selector->matches(
            $this->createClassReflection($className),
        );

        $this->assertSame($expected, $actual);
    }

    public static function classProvider(): iterable
    {
        yield 'missing variable for covered code' => [TestMissingEnvironmentVariableTest::class, true];

        yield 'declared variable for covered code' => [TestDeclaringEnvironmentVariableTest::class, false];

        yield 'dynamic variable name' => [TestCoveringDynamicEnvironmentVariableTest::class, false];

        yield 'direct _ENV usage' => [TestDirectlyUsingEnvTest::class, true];
    }

    public function test_it_collects_environment_variables_from_covered_parent_classes(): void
    {
        $detector = new EnvironmentVariableUsageDetector(
            new Analyser(
                SingletonContainer::getContainer()->getParser(),
                new FileSystem(),
            ),
            $this->getReflectionProvider(),
        );

        $expected = ['LINES', 'COLUMNS', 'SHELL_VERBOSITY'];

        $actual = $detector->getEnvironmentVariables(
            $this->createClassReflection(ApplicationTest::class),
        );

        $this->assertSame($expected, $actual);
    }
}
