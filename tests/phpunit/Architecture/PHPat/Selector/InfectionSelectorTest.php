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

namespace Infection\Tests\Architecture\PHPat\Selector;

use Infection\Benchmark\Instrumentor;
use Infection\Engine;
use Infection\Tests\Architecture\PHPat\ClassesShouldBeFinalTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestNotRequiringIoWithIntegrationGroup\Fixtures\FixtureWithCoveredClassWithoutIoAndIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\CoveredClassWithIo;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoversNothingWithIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoversNothingWithoutIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\PHPUnitTestRequiringIoWithoutIntegrationGroupTest;
use Infection\Tests\AutoReview\ProjectCode\ProjectCodeProvider;
use Infection\Tests\AutoReview\ProjectCode\ProjectCodeTest;
use Infection\Tests\Logger\Console\BasicConsoleLoggerTest;
use LogicException;
use PHPStan\Reflection\ReflectionProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InfectionSelector::class)]
final class InfectionSelectorTest extends SelectorTestCase
{
    public function test_it_matches_phpunit_test_code(): void
    {
        $selector = InfectionSelector::phpunitTestCode();

        $classExpectations = [
            'infection source class' => [Engine::class, false],
            'infection PHPUnit test class' => [self::class, true],
            'infection non-PHPUnit test class' => [InfectionTestCode::class, false],
            'infection benchmark framework class' => [Instrumentor::class, false],
            'vendor class' => [TestCase::class, false],
        ];

        foreach ($classExpectations as $message => [$className, $expected]) {
            $classReflection = $this->createClassReflection($className);

            $actual = $selector->matches($classReflection);

            $this->assertSame($expected, $actual, $message);
        }
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('autoreviewTestCodeClassProvider')]
    public function test_it_matches_autoreview_test_code(
        string $className,
        bool $expected,
    ): void {
        $selector = InfectionSelector::autoreviewTestCode();
        $classReflection = $this->createClassReflection($className);

        $actual = $selector->matches($classReflection);

        $this->assertSame($expected, $actual);
    }

    public static function autoreviewTestCodeClassProvider(): iterable
    {
        yield 'PHPat architecture test class' => [ClassesShouldBeFinalTest::class, true];

        yield 'PHPat custom selector' => [InfectionSelector::class, true];

        yield 'PHPat selector PHPUnit test' => [self::class, true];

        yield 'PHPat selector fixture for PHPUnit tests' => [FixtureWithCoveredClassWithoutIoAndIntegrationGroupTest::class, false];

        yield 'AutoReview PHPunit test' => [ProjectCodeTest::class, true];

        yield 'AutoReview source code' => [ProjectCodeProvider::class, true];

        yield 'infection source class' => [Engine::class, false];

        yield 'regular PHPUnit test class' => [BasicConsoleLoggerTest::class, false];

        yield 'vendor class' => [TestCase::class, false];
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('selectorFixturesClassProvider')]
    public function test_it_matches_selector_fixtures(
        string $className,
        bool $expected,
    ): void {
        $selector = InfectionSelector::selectorFixtures();
        $classReflection = $this->createClassReflection($className);

        $actual = $selector->matches($classReflection);

        $this->assertSame($expected, $actual);
    }

    public static function selectorFixturesClassProvider(): iterable
    {
        yield 'selector test fixture' => [CoveredClassWithIo::class, true];

        yield 'selector test fixtures' => [FixtureWithCoveredClassWithoutIoAndIntegrationGroupTest::class, true];

        yield 'selector test' => [PHPUnitTestRequiringIoWithoutIntegrationGroupTest::class, false];

        yield 'selector class' => [InfectionSelector::class, false];

        yield 'infection PHPUnit test class' => [self::class, false];
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('phpUnitTestsWithCoversNothingClassProvider')]
    public function test_it_matches_phpunit_tests_with_covers_nothing(
        string $className,
        bool $expected,
    ): void {
        $selector = InfectionSelector::phpUnitTestsWithCoversNothing();
        $classReflection = $this->createClassReflection($className);

        $actual = $selector->matches($classReflection);

        $this->assertSame($expected, $actual);
    }

    public static function phpUnitTestsWithCoversNothingClassProvider(): iterable
    {
        yield 'PHPUnit test with CoversNothing' => [FixtureWithCoversNothingWithIntegrationGroupTest::class, true];

        yield 'PHPUnit test without CoversNothing' => [FixtureWithCoveredClassWithoutIoAndIntegrationGroupTest::class, false];

        yield 'PHPat selector class' => [InfectionSelector::class, false];

        yield 'vendor class' => [TestCase::class, false];
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('integrationPhpUnitTestsClassProvider')]
    public function test_it_matches_integration_phpunit_tests(
        string $className,
        bool $expected,
    ): void {
        $selector = InfectionSelector::integrationPhpUnitTests();
        $classReflection = $this->createClassReflection($className);

        $actual = $selector->matches($classReflection);

        $this->assertSame($expected, $actual);
    }

    public static function integrationPhpUnitTestsClassProvider(): iterable
    {
        yield 'PHPUnit test with integration group' => [FixtureWithCoversNothingWithIntegrationGroupTest::class, true];

        yield 'PHPUnit test without integration group' => [FixtureWithCoversNothingWithoutIntegrationGroupTest::class, false];

        yield 'PHPat selector class' => [InfectionSelector::class, false];

        yield 'vendor class' => [TestCase::class, false];
    }

    public function test_it_rejects_different_reflection_providers_for_phpunit_test_io_requirements(): void
    {
        InfectionSelector::phpunitTestRequiringIoWithoutIntegrationGroup($this->getReflectionProvider());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('I/O code detector must be requested with the same reflection provider.');

        InfectionSelector::phpunitTestNotRequiringIoWithIntegrationGroup(
            $this->createStub(ReflectionProvider::class),
        );
    }
}
