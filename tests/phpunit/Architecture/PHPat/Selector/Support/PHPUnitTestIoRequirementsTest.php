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

namespace Infection\Tests\Architecture\PHPat\Selector\Support;

use Infection\FileSystem\FileSystem;
use Infection\Testing\SingletonContainer;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestNotRequiringIoWithIntegrationGroup\Fixtures\FixtureWithCoveredClassWithoutIoAndIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredClassWithFileSystemIoAndDirectIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredClassWithFileSystemIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredClassWithIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredClassWithoutIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredFunctionTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredTraitWithoutIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoversNothingWithIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoversNothingWithoutIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithIoInTestCaseTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithMultipleCoveredClassesTest;
use Infection\Tests\Architecture\PHPat\Selector\SelectorTestCase;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\Analyser;
use Infection\Tests\Command\Debug\DumpAstCommand\DumpAstCommandTest;
use Infection\Tests\FileSystem\Finder\StaticAnalysisToolExecutableFinderTest;
use Infection\Tests\Reporter\FileReporterTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PHPUnitTestIoRequirements::class)]
final class PHPUnitTestIoRequirementsTest extends SelectorTestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('classProvider')]
    public function test_it_detects_phpunit_test_io_requirements(
        string $className,
        bool $expectedRequiresIntegrationGroup,
        bool $expectedHasCoveredClass,
        bool $expectedHasIntegrationGroup,
    ): void {
        $ioRequirements = new PHPUnitTestIoRequirements(
            new Analyser(
                SingletonContainer::getContainer()->getParser(),
                new FileSystem(),
            ),
            $this->getReflectionProvider(),
        );
        $classReflection = $this->createClassReflection($className);

        $this->assertSame(
            $expectedRequiresIntegrationGroup,
            $ioRequirements->requiresIntegrationGroup($classReflection),
        );
        $this->assertSame(
            $expectedHasCoveredClass,
            $ioRequirements->hasCoveredClass($classReflection),
        );
        $this->assertSame(
            $expectedHasIntegrationGroup,
            PHPUnitTestClassAnalysis::belongsToIntegrationGroup($classReflection),
        );
    }

    public static function classProvider(): iterable
    {
        yield 'test with CoversNothing without integration group' => [
            FixtureWithCoversNothingWithoutIntegrationGroupTest::class,
            false,
            false,
            false,
        ];

        yield 'test with CoversNothing with integration group' => [
            FixtureWithCoversNothingWithIntegrationGroupTest::class,
            false,
            false,
            true,
        ];

        yield 'test covering class without I/O' => [
            FixtureWithCoveredClassWithoutIoTest::class,
            false,
            true,
            false,
        ];

        yield 'test covering trait without I/O' => [
            FixtureWithCoveredTraitWithoutIoTest::class,
            false,
            true,
            false,
        ];

        yield 'test covering function' => [
            FixtureWithCoveredFunctionTest::class,
            false,
            false,
            false,
        ];

        yield 'test covering class without I/O with integration group' => [
            FixtureWithCoveredClassWithoutIoAndIntegrationGroupTest::class,
            false,
            true,
            true,
        ];

        yield 'test covering class with I/O' => [
            FixtureWithCoveredClassWithIoTest::class,
            true,
            true,
            false,
        ];

        yield 'test covering class with I/O behind FileSystem abstraction' => [
            FixtureWithCoveredClassWithFileSystemIoTest::class,
            false,
            true,
            false,
        ];

        yield 'test covering class with I/O behind FileSystem abstraction and direct I/O' => [
            FixtureWithCoveredClassWithFileSystemIoAndDirectIoTest::class,
            true,
            true,
            false,
        ];

        yield 'test covering multiple classes with I/O in one class' => [
            FixtureWithMultipleCoveredClassesTest::class,
            true,
            true,
            false,
        ];

        yield 'test case with I/O' => [
            FixtureWithIoInTestCaseTest::class,
            true,
            true,
            false,
        ];

        yield 'test case extending FileSystemTestCase' => [
            StaticAnalysisToolExecutableFinderTest::class,
            true,
            true,
            true,
        ];
    }

    public function test_it_caches_io_detection_per_class(): void
    {
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            // 3 tests cases + their respective covered classes, with one covered class reused
            ->expects($this->exactly(5))
            ->method('readFile')
            ->willReturn('contents');

        $ioRequirements = new PHPUnitTestIoRequirements(
            new Analyser(
                SingletonContainer::getContainer()->getParser(),
                $fileSystemMock,
            ),
            $this->getReflectionProvider(),
        );

        $ioRequirements->requiresIntegrationGroup(
            $this->createClassReflection(FixtureWithCoversNothingWithoutIntegrationGroupTest::class),
        );
        $ioRequirements->requiresIntegrationGroup(
            $this->createClassReflection(FixtureWithCoveredClassWithIoTest::class),
        );
        $ioRequirements->requiresIntegrationGroup(
            $this->createClassReflection(FixtureWithCoveredClassWithIoTest::class),
        );
        $ioRequirements->requiresIntegrationGroup(
            $this->createClassReflection(FixtureWithMultipleCoveredClassesTest::class),
        );
        $ioRequirements->requiresIntegrationGroup(
            $this->createClassReflection(FixtureWithMultipleCoveredClassesTest::class),
        );
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('fileSystemTestCaseChildProvider')]
    public function test_file_system_test_case_children_require_integration_group(string $className): void
    {
        $ioRequirements = new PHPUnitTestIoRequirements(
            new Analyser(
                SingletonContainer::getContainer()->getParser(),
                new FileSystem(),
            ),
            $this->getReflectionProvider(),
        );

        $requiresIntegrationGroup = $ioRequirements->requiresIntegrationGroup(
            $this->createClassReflection($className),
        );

        $this->assertTrue($requiresIntegrationGroup);
    }

    public static function fileSystemTestCaseChildProvider(): iterable
    {
        yield DumpAstCommandTest::class => [DumpAstCommandTest::class];

        yield FileReporterTest::class => [FileReporterTest::class];
    }
}
