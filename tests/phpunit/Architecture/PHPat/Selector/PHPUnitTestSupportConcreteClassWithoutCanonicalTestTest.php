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

use Infection\Command\ConfigureCommand;
use Infection\Engine;
use Infection\Tests\Architecture\PHPat\Selector\HasDocBlock\ClassWithDocBlock;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\Analyser;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\AnalysisResult;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\DetectConcreteClassMeaningfulImplementationVisitor;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Configuration\ConfigurationFactory\ConfigurationFactoryScenario;
use Infection\Tests\Configuration\ProjectDirectoryProvider\FixedProjectDirectoryProvider;
use Infection\Tests\Mutator\MutatorFixturesProvider;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\ConcreteVisitorTestCase;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use Infection\Tests\Process\Exception\GenericProcessException;
use Infection\Tests\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider\PhpUnit09Provider;
use Infection\Tests\TestFramework\Tracing\Tracer\CodeceptionProvider;
use Infection\Tests\TestingUtility\FS;
use Infection\Tests\TestingUtility\Iterable\NonRewindableIterator;
use Infection\Tests\TestingUtility\Iterable\NonRewindableIteratorTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PHPUnitTestSupportConcreteClassWithoutCanonicalTest::class)]
#[CoversClass(HasTrivialImplementation::class)]
#[CoversClass(AnalysisResult::class)]
#[CoversClass(Support\ConcreteClassReflection::class)]
#[CoversClass(Analyser::class)]
#[CoversClass(DetectConcreteClassMeaningfulImplementationVisitor::class)]
final class PHPUnitTestSupportConcreteClassWithoutCanonicalTestTest extends SelectorTestCase
{
    public function test_it_does_not_match_anonymous_classes(): void
    {
        $selector = new PHPUnitTestSupportConcreteClassWithoutCanonicalTest();
        $classReflection = $this->createAnonymousClassReflectionFromFile(__DIR__ . '/IsAnonymousClass/source-anonymous-class.php');

        $actual = $selector->matches($classReflection);

        $this->assertFalse($actual);
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('classProvider')]
    public function test_it_matches_phpunit_test_support_concrete_classes_without_canonical_test(
        string $className,
        bool $expected,
    ): void {
        $selector = new PHPUnitTestSupportConcreteClassWithoutCanonicalTest();
        $classReflection = $this->createClassReflection($className);

        $actual = $selector->matches($classReflection);

        $this->assertSame($expected, $actual);
    }

    public static function classProvider(): iterable
    {
        yield 'testing utility without canonical test' => [
            FS::class,
            true,
        ];

        yield 'test support class with empty implementation' => [
            GenericProcessException::class,
            false,
        ];

        yield 'testing utility with canonical test' => [
            NonRewindableIterator::class,
            false,
        ];

        yield 'testing utility test' => [
            NonRewindableIteratorTest::class,
            false,
        ];

        yield 'untested concrete PHPUnit test case helper' => [
            ConcreteVisitorTestCase::class,
            true,
        ];

        yield 'tested PHPUnit test case helper' => [
            VisitorTestCase::class,
            false,
        ];

        yield 'PHPUnit data provider' => [
            PhpUnit09Provider::class,
            false,
        ];

        yield 'PHPUnit test fixture namespace' => [
            ClassWithDocBlock::class,
            false,
        ];

        yield 'PHPUnit test fixture class' => [
            ConfigurationFactoryScenario::class,
            false,
        ];

        yield 'PHPUnit test fixture provider' => [
            MutatorFixturesProvider::class,
            false,
        ];

        yield 'another PHPUnit test fixture provider' => [
            CodeceptionProvider::class,
            false,
        ];

        yield 'test support class outside TestingUtility without canonical test' => [
            FixedProjectDirectoryProvider::class,
            true,
        ];

        yield 'test support class outside TestingUtility with canonical test' => [
            ConfigurationBuilder::class,
            false,
        ];

        yield 'source class without canonical test' => [
            ConfigureCommand::class,
            false,
        ];

        yield 'source class with canonical test' => [
            Engine::class,
            false,
        ];
    }
}
