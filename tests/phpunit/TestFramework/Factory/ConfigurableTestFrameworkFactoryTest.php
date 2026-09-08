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

namespace Infection\Tests\TestFramework\Factory;

use Infection\TestFramework\Contracts\TestFramework;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurableTestFrameworkFactory::class)]
final class ConfigurableTestFrameworkFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        ConfigurableTestFrameworkFactory::reset();
    }

    /**
     * @return iterable<string, array{callable(): mixed}>
     */
    public static function provideUnconfiguredOperation(): iterable
    {
        yield 'create test framework' => [
            static fn () => ConfigurableTestFrameworkFactory::create('', '', '', null, '', '', [], false),
        ];

        yield 'get adapter name' => [ConfigurableTestFrameworkFactory::getAdapterName(...)];

        yield 'get executable name' => [ConfigurableTestFrameworkFactory::getExecutableName(...)];
    }

    public function test_it_can_be_configured(): void
    {
        $expectedTestFramework = $this->createStub(TestFramework::class);

        ConfigurableTestFrameworkFactory::configure(
            $expectedTestFramework,
            'dummy',
            'dummy-executable',
        );

        $actualTestFramework = ConfigurableTestFrameworkFactory::create(
            '',
            '',
            '',
            null,
            '',
            '',
            [],
            false,
        );

        $this->assertSame($expectedTestFramework, $actualTestFramework);
        $this->assertSame('dummy', ConfigurableTestFrameworkFactory::getAdapterName());
        $this->assertSame('dummy-executable', ConfigurableTestFrameworkFactory::getExecutableName());
    }

    #[DataProvider('provideUnconfiguredOperation')]
    public function test_it_cannot_be_used_without_being_configured(callable $operation): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'TestFrameworkFactory is not configured. Call configure() before using it',
            ),
        );

        $operation();
    }

    public function test_it_cannot_be_configured_twice_without_reset(): void
    {
        $firstTestFramework = $this->createStub(TestFramework::class);
        $secondTestFramework = $this->createStub(TestFramework::class);

        ConfigurableTestFrameworkFactory::configure(
            $firstTestFramework,
            'first',
            'first-executable',
        );

        $this->expectExceptionObject(
            new InvalidArgumentException('TestFrameworkFactory is already configured'),
        );

        ConfigurableTestFrameworkFactory::configure(
            $secondTestFramework,
            'second',
            'second-executable',
        );
    }

    public function test_it_can_be_configured_again_after_reset(): void
    {
        $firstTestFramework = $this->createStub(TestFramework::class);
        $expectedTestFramework = $this->createStub(TestFramework::class);

        ConfigurableTestFrameworkFactory::configure(
            $firstTestFramework,
            'first',
            'first-executable',
        );
        ConfigurableTestFrameworkFactory::reset();
        ConfigurableTestFrameworkFactory::configure(
            $expectedTestFramework,
            'second',
            'second-executable',
        );

        $actualTestFramework = ConfigurableTestFrameworkFactory::create(
            '',
            '',
            '',
            null,
            '',
            '',
            [],
            false,
        );

        $this->assertSame($expectedTestFramework, $actualTestFramework);
        $this->assertSame('second', ConfigurableTestFrameworkFactory::getAdapterName());
        $this->assertSame('second-executable', ConfigurableTestFrameworkFactory::getExecutableName());
    }
}
