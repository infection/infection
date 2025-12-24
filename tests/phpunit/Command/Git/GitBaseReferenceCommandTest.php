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
use Infection\Command\Git\GitDefaultBaseCommand;
use Infection\Console\Application;
use Infection\Container;
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
            ->expects($this->once())
            ->method('getBaseReference')
            ->with('origin/main')
            ->willReturn('8af25a159143aadacf4d875a3114014e99053430');

        $tester = $this->createCommandTester();

        $result = $tester->execute([
            '--base' => 'origin/main',
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);

        $this->assertSame(0, $result);
        $this->assertSame("8af25a159143aadacf4d875a3114014e99053430\n", $tester->getDisplay());
        $this->assertSame('', $tester->getErrorOutput());
    }

    public function test_it_outputs_the_base_reference_with_default_base(): void
    {
        $this->git
            ->expects($this->once())
            ->method('getDefaultBase')
            ->willReturn('origin/master');
        $this->git
            ->expects($this->once())
            ->method('getBaseReference')
            ->with('origin/master')
            ->willReturn('abc123def456');

        $tester = $this->createCommandTester();

        $result = $tester->execute([], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);

        $this->assertSame(0, $result);
        $this->assertSame("abc123def456\n", $tester->getDisplay());
        $this->assertStringContainsString('[notice] No base found.', $tester->getErrorOutput());
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

        $result = $tester->execute([
            '--base' => '  origin/develop  ',
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);

        $this->assertSame(0, $result);
        $this->assertSame("def456abc789\n", $tester->getDisplay());
        $this->assertSame('', $tester->getErrorOutput());
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

        $tester->execute([
            '--base' => '   ',
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);
    }

    public function test_it_accepts_commit_hash_as_base(): void
    {
        $this->git
            ->expects($this->never())
            ->method('getDefaultBase');
        $this->git
            ->expects($this->once())
            ->method('getBaseReference')
            ->with('abc123')
            ->willReturn('abc123def456');

        $tester = $this->createCommandTester();

        $result = $tester->execute([
            '--base' => 'abc123',
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);

        $this->assertSame(0, $result);
        $this->assertSame("abc123def456\n", $tester->getDisplay());
        $this->assertSame('', $tester->getErrorOutput());
    }

    public function test_it_accepts_full_ref_name_as_base(): void
    {
        $this->git
            ->expects($this->never())
            ->method('getDefaultBase');
        $this->git
            ->expects($this->once())
            ->method('getBaseReference')
            ->with('refs/remotes/origin/main')
            ->willReturn('123abc456def');

        $tester = $this->createCommandTester();

        $result = $tester->execute([
            '--base' => 'refs/remotes/origin/main',
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);

        $this->assertSame(0, $result);
        $this->assertSame("123abc456def\n", $tester->getDisplay());
        $this->assertSame('', $tester->getErrorOutput());
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
