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

namespace Infection\Tests\Command\Git;

use Infection\Command\Git\GitBaseReferenceCommand;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\Git\Git;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(GitBaseReferenceCommand::class)]
final class GitBaseReferenceCommandTest extends TestCase
{
    private Git&MockObject $git;

    protected function setUp(): void
    {
        $this->git = $this->createMock(Git::class);
    }

    public function test_it_outputs_the_base_reference_with_provided_base(): void
    {
        $this->git
            ->expects($this->never())
            ->method('getDefaultBase');
        $this->git
            ->method('getBaseReference')
            ->with('origin/main')
            ->willReturn('8af25a159143aadacf4d875a3114014e99053430');

        $expectedStdout = <<<'STDOUT'
            8af25a159143aadacf4d875a3114014e99053430

            STDOUT;
        $expectedStderr = '';
        $expectedDisplay = <<<'DISPLAY'
            8af25a159143aadacf4d875a3114014e99053430

            DISPLAY;

        $tester = $this->createCommandTester();

        $tester->execute(
            ['--base' => 'origin/main'],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE],
        );

        $tester->assertCommandIsSuccessful();
        $this->assertSame($expectedDisplay, $tester->getDisplay(true));

        $tester->execute(
            ['--base' => 'origin/main'],
            [
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
                'capture_stderr_separately' => true,
            ],
        );

        $tester->assertCommandIsSuccessful();
        $this->assertSame($expectedStdout, $tester->getDisplay(true));
        $this->assertSame($expectedStderr, $tester->getErrorOutput(true));
    }

    public function test_it_trims_the_base_option(): void
    {
        $this->git
            ->expects($this->never())
            ->method('getDefaultBase');
        $this->git
            ->expects($this->once())
            ->method('getBaseReference')
            ->with('origin/develop')
            ->willReturn('def456abc789');

        $tester = $this->createCommandTester();

        $tester->execute(['--base' => '  origin/develop  ']);

        $tester->assertCommandIsSuccessful();
    }

    public function test_it_rejects_blank_base_option(): void
    {
        $this->git
            ->expects($this->never())
            ->method('getDefaultBase');
        $this->git
            ->expects($this->never())
            ->method('getBaseReference');

        $tester = $this->createCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a non-blank value for the option "--base".');

        $tester->execute(['--base' => '   ']);
    }

    private function createCommandTester(): CommandTester
    {
        $container = Container::create();
        $container->set(Git::class, fn () => $this->git);

        $application = new Application($container);

        $command = new GitBaseReferenceCommand();
        $command->setApplication($application);

        return new CommandTester($command);
    }
}
