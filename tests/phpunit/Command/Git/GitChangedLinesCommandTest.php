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

use Infection\Command\Git\GitChangedFilesCommand;
use Infection\Command\Git\GitChangedLinesCommand;
use Infection\Console\Application;
use Infection\Container;
use Infection\Differ\ChangedLinesRange;
use Infection\Git\Git;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(GitChangedLinesCommand::class)]
final class GitChangedLinesCommandTest extends TestCase
{
    private Git&MockObject $git;

    protected function setUp(): void
    {
        $this->git = $this->createMock(Git::class);
    }

    public function test_it_outputs_changed_lines_with_provided_base_and_default_filter(): void
    {
        $this->git
            ->expects($this->never())
            ->method('getDefaultBase');
        $this->git
            ->expects($this->once())
            ->method('getBaseReference')
            ->with('origin/main')
            ->willReturn('abc123');
        $this->git
            ->expects($this->once())
            ->method('getChangedLinesRangesByFileRelativePaths')
            ->with('AM', 'abc123', ['src'])
            ->willReturn([
                'src/File1.php' => [ChangedLinesRange::create(1, 5), ChangedLinesRange::create(10, 15)],
                'src/File2.php' => [ChangedLinesRange::create(20, 20)],
            ]);

        $tester = $this->createCommandTester();

        $result = $tester->execute([
            '--base' => 'origin/main',
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);

        $this->assertSame(0, $result);
        $this->assertSame("src/File1.php: [1,5]\nsrc/File1.php: [10,15]\nsrc/File2.php: [20,20]\n", $tester->getDisplay());
        $this->assertStringContainsString('[notice] Using the reference', $tester->getErrorOutput());
    }

    public function test_it_outputs_changed_lines_with_default_base_and_default_filter(): void
    {
        $this->git
            ->expects($this->atLeastOnce())
            ->method('getDefaultBase')
            ->willReturn('origin/master');
        $this->git
            ->expects($this->once())
            ->method('getBaseReference')
            ->with('origin/master')
            ->willReturn('def456');
        $this->git
            ->expects($this->once())
            ->method('getChangedLinesRangesByFileRelativePaths')
            ->with('AM', 'def456', ['src'])
            ->willReturn([
                'tests/File1Test.php' => [ChangedLinesRange::create(1, 10)],
            ]);

        $tester = $this->createCommandTester();

        $result = $tester->execute([], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);

        $this->assertSame(0, $result);
        $this->assertSame("tests/File1Test.php: [1,10]\n", $tester->getDisplay());
        $this->assertStringContainsString('[notice] No base found.', $tester->getErrorOutput());
        $this->assertStringContainsString('[notice] Using the reference', $tester->getErrorOutput());
    }

    public function test_it_trims_the_base_option(): void
    {
        $this->git
            ->expects($this->never())
            ->method('getDefaultBase');
        $this->git
            ->expects($this->once())
            ->method('getBaseReference')
            ->with('feature/test')
            ->willReturn('xyz123');
        $this->git
            ->expects($this->once())
            ->method('getChangedLinesRangesByFileRelativePaths')
            ->with('AM', 'xyz123', ['src'])
            ->willReturn([
                'src/Test.php' => [ChangedLinesRange::create(1, 1)],
            ]);

        $tester = $this->createCommandTester();

        $result = $tester->execute([
            '--base' => '  feature/test  ',
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);

        $this->assertSame(0, $result);
        $this->assertSame("src/Test.php: [1,1]\n", $tester->getDisplay());
        $this->assertStringContainsString('[notice] Using the reference', $tester->getErrorOutput());
    }

    public function test_it_trims_the_filter_option(): void
    {
        $this->git
            ->expects($this->never())
            ->method('getDefaultBase');
        $this->git
            ->expects($this->once())
            ->method('getBaseReference')
            ->with('origin/main')
            ->willReturn('abc999');
        $this->git
            ->expects($this->once())
            ->method('getChangedLinesRangesByFileRelativePaths')
            ->with('D', 'abc999', ['src'])
            ->willReturn([
                'src/Deleted.php' => [ChangedLinesRange::create(1, 100)],
            ]);

        $tester = $this->createCommandTester();

        $result = $tester->execute([
            '--base' => 'origin/main',
            '--filter' => '  D  ',
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);

        $this->assertSame(0, $result);
        $this->assertSame("src/Deleted.php: [1,100]\n", $tester->getDisplay());
        $this->assertStringContainsString('[notice] Using the reference', $tester->getErrorOutput());
    }

    public function test_it_rejects_blank_base_option(): void
    {
        $this->git
            ->expects($this->never())
            ->method('getDefaultBase');
        $this->git
            ->expects($this->never())
            ->method('getBaseReference');
        $this->git
            ->expects($this->never())
            ->method('getChangedLinesRangesByFileRelativePaths');

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

    public function test_it_rejects_blank_filter_option(): void
    {
        $this->git
            ->expects($this->never())
            ->method('getDefaultBase');
        $this->git
            ->expects($this->never())
            ->method('getBaseReference');
        $this->git
            ->expects($this->never())
            ->method('getChangedLinesRangesByFileRelativePaths');

        $tester = $this->createCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a non-blank value for the option "--base".');

        $tester->execute([
            '--base' => 'origin/main',
            '--filter' => '   ',
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            'capture_stderr_separately' => true,
        ]);
    }

    public function test_print_changed_lines_outputs_single_range(): void
    {
        $output = new BufferedOutput();

        GitChangedLinesCommand::printChangedLines(
            [
                'src/File.php' => [ChangedLinesRange::create(5, 10)],
            ],
            $output,
        );

        $display = $output->fetch();
        $this->assertStringContainsString('src/File.php: [5,10]', $display);
    }

    public function test_print_changed_lines_outputs_multiple_ranges_for_same_file(): void
    {
        $output = new BufferedOutput();

        GitChangedLinesCommand::printChangedLines(
            [
                'src/File.php' => [
                    ChangedLinesRange::create(1, 5),
                    ChangedLinesRange::create(10, 15),
                    ChangedLinesRange::create(20, 20),
                ],
            ],
            $output,
        );

        $display = $output->fetch();
        $this->assertStringContainsString('src/File.php: [1,5]', $display);
        $this->assertStringContainsString('src/File.php: [10,15]', $display);
        $this->assertStringContainsString('src/File.php: [20,20]', $display);
    }

    public function test_print_changed_lines_outputs_multiple_files(): void
    {
        $output = new BufferedOutput();

        GitChangedLinesCommand::printChangedLines(
            [
                'src/File1.php' => [ChangedLinesRange::create(1, 2)],
                'src/File2.php' => [ChangedLinesRange::create(3, 4)],
                'tests/FileTest.php' => [ChangedLinesRange::create(10, 20)],
            ],
            $output,
        );

        $display = $output->fetch();
        $this->assertStringContainsString('src/File1.php: [1,2]', $display);
        $this->assertStringContainsString('src/File2.php: [3,4]', $display);
        $this->assertStringContainsString('tests/FileTest.php: [10,20]', $display);
    }

    public function test_print_changed_lines_outputs_single_line_range(): void
    {
        $output = new BufferedOutput();

        GitChangedLinesCommand::printChangedLines(
            [
                'src/SingleLine.php' => [ChangedLinesRange::create(42, 42)],
            ],
            $output,
        );

        $display = $output->fetch();
        $this->assertStringContainsString('src/SingleLine.php: [42,42]', $display);
    }

    private function createCommandTester(): CommandTester
    {
        $container = Container::create();
        $container->set(Git::class, fn () => $this->git);

        $application = new Application($container);

        $command = new GitChangedLinesCommand();
        $command->setApplication($application);

        return new CommandTester($command);
    }
}
