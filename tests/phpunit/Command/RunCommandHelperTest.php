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

namespace Infection\Tests\Command;

use Infection\Command\RunCommand;
use Infection\Command\RunCommandHelper;
use Infection\Container\Container;
use Infection\TestFramework\MapSourceClassToTestStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

#[CoversClass(RunCommandHelper::class)]
final class RunCommandHelperTest extends TestCase
{
    private InputInterface&MockObject $inputMock;

    protected function setUp(): void
    {
        $this->inputMock = $this->createMock(InputInterface::class);
    }

    #[DataProvider('providesUsesGitHubLogger')]
    public function test_it_uses_github_logger(?bool $expected, mixed $optionValue): void
    {
        $this->inputMock->expects($this->once())
            ->method('getOption')
            ->with(RunCommand::OPTION_LOGGER_GITHUB)
            ->willReturn($optionValue);

        $commandHelper = new RunCommandHelper($this->inputMock);
        $this->assertSame($expected, $commandHelper->getUseGitHubLogger());
    }

    public static function providesUsesGitHubLogger(): iterable
    {
        yield [null, false];

        yield [true, null];

        yield [true, 'true'];

        yield [false, 'false'];
    }

    #[DataProvider('providesThreadCount')]
    public function test_thread_count_from_option(?int $expected, mixed $optionValue): void
    {
        $this->inputMock->expects($this->once())
            ->method('getOption')
            ->with(RunCommand::OPTION_THREADS)
            ->willReturn($optionValue);

        $commandHelper = new RunCommandHelper($this->inputMock);
        $this->assertSame($expected, $commandHelper->getThreadCount());
    }

    public static function providesThreadCount(): iterable
    {
        yield [null, null];

        yield [5, '5'];
    }

    #[DataProvider('providesMapSourceClassToTest')]
    public function test_it_maps_source_class_to_test(?string $expected, mixed $optionValue): void
    {
        $this->inputMock->expects($this->once())
            ->method('getOption')
            ->with(RunCommand::OPTION_MAP_SOURCE_CLASS_TO_TEST)
            ->willReturn($optionValue);

        $commandHelper = new RunCommandHelper($this->inputMock);
        $this->assertSame($expected, $commandHelper->getMapSourceClassToTest());
    }

    public static function providesMapSourceClassToTest(): iterable
    {
        yield [null, false];

        yield [MapSourceClassToTestStrategy::SIMPLE, null];

        yield [MapSourceClassToTestStrategy::SIMPLE, MapSourceClassToTestStrategy::SIMPLE];
    }

    #[DataProvider('providesNumberOfShownMutations')]
    public function test_it_returns_number_of_shown_mutations(?int $expected, mixed $optionValue): void
    {
        $this->inputMock->expects($this->once())
            ->method('getOption')
            ->with(RunCommand::OPTION_SHOW_MUTATIONS)
            ->willReturn($optionValue);

        $commandHelper = new RunCommandHelper($this->inputMock);
        $this->assertSame($expected, $commandHelper->getNumberOfShownMutations());
    }

    public static function providesNumberOfShownMutations(): iterable
    {
        yield [Container::DEFAULT_SHOW_MUTATIONS, null];

        yield [5, '5'];

        yield [null, 'max'];
    }

    #[DataProvider('providesIgnoreMsiWithNoMutations')]
    public function test_it_returns_ignore_msi_with_no_mutations(?bool $expected, mixed $optionValue): void
    {
        $this->inputMock->expects($this->once())
            ->method('getOption')
            ->with(RunCommand::OPTION_IGNORE_MSI_WITH_NO_MUTATIONS)
            ->willReturn($optionValue);

        $commandHelper = new RunCommandHelper($this->inputMock);
        $this->assertSame($expected, $commandHelper->getIgnoreMsiWithNoMutations());
    }

    public static function providesIgnoreMsiWithNoMutations(): iterable
    {
        yield 'not provided' => [null, RunCommand::OPTION_VALUE_NOT_PROVIDED];

        yield 'provided without value' => [true, null];

        yield 'provided with value 1' => [true, '1'];

        yield 'provided with value true' => [true, 'true'];
    }

    #[DataProvider('providesTimeoutsAsEscaped')]
    public function test_it_returns_timeouts_as_escaped(bool $expected, mixed $optionValue): void
    {
        $this->inputMock->expects($this->once())
            ->method('getOption')
            ->with(RunCommand::OPTION_WITH_TIMEOUTS)
            ->willReturn($optionValue);

        $commandHelper = new RunCommandHelper($this->inputMock);
        $this->assertSame($expected, $commandHelper->getTimeoutsAsEscaped());
    }

    public static function providesTimeoutsAsEscaped(): iterable
    {
        yield 'not provided (VALUE_NONE returns false)' => [false, false];

        yield 'provided (VALUE_NONE returns true)' => [true, true];
    }

    #[DataProvider('providesMaxTimeouts')]
    public function test_it_returns_max_timeouts(?int $expected, mixed $optionValue): void
    {
        $this->inputMock->expects($this->once())
            ->method('getOption')
            ->with(RunCommand::OPTION_MAX_TIMEOUTS)
            ->willReturn($optionValue);

        $commandHelper = new RunCommandHelper($this->inputMock);
        $this->assertSame($expected, $commandHelper->getMaxTimeouts());
    }

    public static function providesMaxTimeouts(): iterable
    {
        yield 'not provided' => [null, null];

        yield 'provided as string 5' => [5, '5'];

        yield 'provided as string 0' => [0, '0'];

        yield 'provided as string 100' => [100, '100'];
    }

    #[DataProvider('providesGetStringOption')]
    public function test_it_returns_string_option(?string $expected, mixed $optionValue, ?string $default = null): void
    {
        $this->inputMock->expects($this->once())
            ->method('getOption')
            ->with('test-option')
            ->willReturn($optionValue);

        $commandHelper = new RunCommandHelper($this->inputMock);
        $this->assertSame($expected, $commandHelper->getStringOption('test-option', $default));
    }

    public static function providesGetStringOption(): iterable
    {
        yield 'null returns null' => [null, null];

        yield 'empty string returns null' => [null, ''];

        yield 'whitespace-only returns null' => [null, '   '];

        yield 'non-empty string returns trimmed' => ['path/to/file.json', 'path/to/file.json'];

        yield 'string with leading/trailing whitespace returns trimmed' => ['path/to/file.json', '  path/to/file.json  '];

        yield 'empty with default returns default' => ['default.json', '', 'default.json'];

        yield 'null with default returns default' => ['default.json', null, 'default.json'];

        yield 'value with default returns value' => ['custom.json', 'custom.json', 'default.json'];
    }
}
