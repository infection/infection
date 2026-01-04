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
use Infection\Container;
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
}
