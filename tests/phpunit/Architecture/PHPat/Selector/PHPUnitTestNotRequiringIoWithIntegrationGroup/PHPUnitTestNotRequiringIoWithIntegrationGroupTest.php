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

namespace Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestNotRequiringIoWithIntegrationGroup;

use Infection\FileSystem\FileSystem;
use Infection\Testing\SingletonContainer;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestNotRequiringIoWithIntegrationGroup;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestNotRequiringIoWithIntegrationGroup\Fixtures\FixtureWithCoveredClassWithoutIoAndIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredClassWithIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoveredClassWithoutIoTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithCoversNothingWithIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestRequiringIoWithoutIntegrationGroup\Fixtures\FixtureWithIoInTestCaseTest;
use Infection\Tests\Architecture\PHPat\Selector\SelectorTestCase;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\Analyser;
use Infection\Tests\Architecture\PHPat\Selector\Support\IoCodeDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PHPUnitTestNotRequiringIoWithIntegrationGroup::class)]
final class PHPUnitTestNotRequiringIoWithIntegrationGroupTest extends SelectorTestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('classProvider')]
    public function test_it_matches_phpunit_tests_not_requiring_io_with_the_integration_group(
        string $className,
        bool $expected,
    ): void {
        $selector = new PHPUnitTestNotRequiringIoWithIntegrationGroup(
            new IoCodeDetector(
                new Analyser(
                    SingletonContainer::getContainer()->getParser(),
                    new FileSystem(),
                ),
                $this->getReflectionProvider(),
            ),
        );
        $classReflection = $this->createClassReflection($className);

        $actual = $selector->matches($classReflection);

        $this->assertSame($expected, $actual);
    }

    public static function classProvider(): iterable
    {
        yield 'test covering class without I/O with integration group' => [
            FixtureWithCoveredClassWithoutIoAndIntegrationGroupTest::class,
            false,
        ];

        yield 'test covering class without I/O without integration group' => [
            FixtureWithCoveredClassWithoutIoTest::class,
            false,
        ];

        yield 'test with CoversNothing with integration group' => [
            FixtureWithCoversNothingWithIntegrationGroupTest::class,
            false,
        ];

        yield 'test covering class with I/O without integration group' => [
            FixtureWithCoveredClassWithIoTest::class,
            false,
        ];

        yield 'test case with I/O' => [
            FixtureWithIoInTestCaseTest::class,
            false,
        ];

        yield 'vendor test case' => [
            TestCase::class,
            false,
        ];
    }
}
