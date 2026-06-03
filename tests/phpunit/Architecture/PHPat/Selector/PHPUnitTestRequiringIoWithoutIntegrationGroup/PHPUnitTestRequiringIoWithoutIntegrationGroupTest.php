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

namespace Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup;

use Infection\FileSystem\FileSystem;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredClassWithFileSystemIoAndDirectIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredClassWithFileSystemIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredClassWithIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredClassWithoutIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoversNothingWithIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoversNothingWithoutIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithIoInTestCaseTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithMultipleCoveredClassesTest;
use Infection\Tests\Architecture\PHPat\Selector\SelectorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(PHPUnitTestRequiringIoWithoutIntegrationGroup::class)]
#[Group('integration')]
final class PHPUnitTestRequiringIoWithoutIntegrationGroupTest extends SelectorTestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('classProvider')]
    public function test_it_matches_phpunit_tests_requiring_io_without_the_integration_group(
        string $className,
        bool $expected,
    ): void {
        $selector = new PHPUnitTestRequiringIoWithoutIntegrationGroup(
            new FileSystem(),
        );
        $classReflection = $this->createClassReflection($className);

        $actual = $selector->matches($classReflection);

        $this->assertSame($expected, $actual);
    }

    public static function classProvider(): iterable
    {
        yield 'test with CoversNothing' => [
            FixtureWithCoversNothingWithoutIntegrationGroupTest::class,
            true,
        ];

        yield 'test without CoversClass with integration group' => [
            FixtureWithCoversNothingWithIntegrationGroupTest::class,
            false,
        ];

        yield 'test covering class without I/O' => [
            FixtureWithCoveredClassWithoutIoTest::class,
            false,
        ];

        yield 'test covering class with I/O' => [
            FixtureWithCoveredClassWithIoTest::class,
            true,
        ];

        yield 'test covering class with I/O behind FileSystem abstraction' => [
            FixtureWithCoveredClassWithFileSystemIoTest::class,
            true,
        ];

        yield 'test covering class with I/O behind FileSystem abstraction and direct I/O' => [
            FixtureWithCoveredClassWithFileSystemIoAndDirectIoTest::class,
            true,
        ];

        yield 'test covering multiple classes with I/O in one class' => [
            FixtureWithMultipleCoveredClassesTest::class,
            true,
        ];

        yield 'test case with I/O' => [
            FixtureWithIoInTestCaseTest::class,
            true,
        ];

        yield 'vendor test case' => [
            TestCase::class,
            false,
        ];
    }

    public function test_it_caches_io_detection_per_class(): void
    {
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            // 3 tests cases + their respective covered
            ->expects($this->exactly(5))
            ->method('readFile')
            ->willReturn('contents');

        $selector = new PHPUnitTestRequiringIoWithoutIntegrationGroup($fileSystemMock);

        $selector->matches($this->createClassReflection(FixtureWithCoversNothingWithoutIntegrationGroupTest::class));
        $selector->matches($this->createClassReflection(FixtureWithCoveredClassWithIoTest::class));
        $selector->matches($this->createClassReflection(FixtureWithCoveredClassWithIoTest::class));
        $selector->matches($this->createClassReflection(FixtureWithMultipleCoveredClassesTest::class));
        $selector->matches($this->createClassReflection(FixtureWithMultipleCoveredClassesTest::class));
    }
}
