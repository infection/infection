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

use Infection\Command\ConfigureCommand;
use Infection\Tests\Architecture\PHPat\Selector\PHPUnitTestNotRequiringIoWithIntegrationGroup\Fixtures\FixtureWithCoveredClassWithoutIoAndIntegrationGroupTest;
use Infection\Tests\Architecture\PHPat\Selector\SelectorTestCase;
use Infection\Tests\Architecture\PHPat\Selector\Support\Fixtures\PHPUnitTestWithUnitGroupFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PHPUnitTestClassAnalysis::class)]
final class PHPUnitTestClassAnalysisTest extends SelectorTestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('phpUnitTestCaseProvider')]
    public function test_it_detects_phpunit_test_cases(
        string $className,
        bool $expected,
    ): void {
        $actual = PHPUnitTestClassAnalysis::isPHPUnitTestCase(
            $this->createClassReflection($className),
        );

        $this->assertSame($expected, $actual);
    }

    public static function phpUnitTestCaseProvider(): iterable
    {
        yield 'PHPUnit test case' => [
            self::class,
            true,
        ];

        yield 'source class' => [
            ConfigureCommand::class,
            false,
        ];
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('integrationGroupProvider')]
    public function test_it_detects_integration_group(
        string $className,
        bool $expected,
    ): void {
        $actual = PHPUnitTestClassAnalysis::belongsToIntegrationGroup(
            $this->createClassReflection($className),
        );

        $this->assertSame($expected, $actual);
    }

    public static function integrationGroupProvider(): iterable
    {
        yield 'positional integration group' => [
            FixtureWithCoveredClassWithoutIoAndIntegrationGroupTest::class,
            true,
        ];

        yield 'non-integration group' => [
            PHPUnitTestWithUnitGroupFixture::class,
            false,
        ];

        yield 'no group' => [
            self::class,
            false,
        ];
    }
}
