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

namespace Infection\Tests\Container\Builder;

use Infection\Container\Builder\TestFrameworkBuilder;
use Infection\Container\Container;
use Infection\TestFramework\CombinedTestFramework;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Factory;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\Testing\SingletonContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(TestFrameworkBuilder::class)]
#[CoversClass(Container::class)]
#[CoversClass(Factory::class)]
#[Group('integration')]
final class TestFrameworkBuilderTest extends TestCase
{
    /**
     * @param class-string<TestFramework> $expectedClass
     */
    #[DataProvider('frameworkProvider')]
    public function test_it_builds_the_configured_frameworks(
        string $testFramework,
        ?string $staticAnalysisTool,
        string $expectedClass,
        string $expectedName,
    ): void {
        $container = SingletonContainer::getContainer()->withValues(
            new NullLogger(),
            new NullOutput(),
            testFramework: $testFramework,
            staticAnalysisTool: $staticAnalysisTool,
        );

        $framework = $container->getTestFramework();

        $this->assertInstanceOf($expectedClass, $framework);

        if ($staticAnalysisTool === null) {
            $this->assertNotInstanceOf(CombinedTestFramework::class, $framework);
        }

        $this->assertSame(
            $expectedName,
            $framework->getName(),
        );
        $this->assertSame(
            $framework,
            $container->getTestFramework(),
        );
    }

    public static function frameworkProvider(): iterable
    {
        yield 'PHPUnit without static analysis' => [
            'phpunit', null, PhpUnitAdapter::class, 'PHPUnit',
        ];

        yield 'legacy adapter without static analysis' => [
            'debug', null, TestFramework::class, 'Debug',
        ];

        yield 'PHPUnit followed by PHPStan' => [
            'phpunit', 'phpstan', CombinedTestFramework::class, 'PHPUnit, PHPStan',
        ];

        yield 'PHPUnit followed by debug static analysis' => [
            'phpunit', 'debug', CombinedTestFramework::class, 'PHPUnit, Debug',
        ];

        yield 'legacy adapter followed by static analysis' => [
            'debug', 'debug', CombinedTestFramework::class, 'Debug, Debug',
        ];
    }
}
