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

namespace Infection\Tests\Command\ListSourcesCommand;

use const DIRECTORY_SEPARATOR;
use Infection\Command\ListSourcesCommand;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\Framework\OperatingSystem;
use Infection\Framework\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\chdir;
use function Safe\getcwd;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('integration')]
#[CoversClass(ListSourcesCommand::class)]
final class ListSourcesCommandTest extends TestCase
{
    private const string FIXTURES_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures';

    private string $cwd;

    protected function setUp(): void
    {
        $this->cwd = getcwd();
        chdir(self::FIXTURES_DIR);
    }

    protected function tearDown(): void
    {
        chdir($this->cwd);
    }

    /**
     * @param list<string> $paths
     */
    #[DataProvider('sourcePathsProvider')]
    public function test_it_lists_all_source_files(
        array $paths,
        string $expectedStdout,
        string $expectedStderr,
    ): void {
        $tester = $this->runCommand([
            'paths' => $paths,
        ]);

        $tester->assertCommandIsSuccessful();
        $this->assertSame(
            $expectedStdout,
            Str::toUnixLineEndings($tester->getDisplay()),
        );
        $this->assertSame(
            $expectedStderr,
            $tester->getErrorOutput(normalize: true),
        );
    }

    public static function sourcePathsProvider(): iterable
    {
        $expectedStdout = <<<'STDOUT'
            File1.php
            File2.php
            SubDir/File3.php

            STDOUT;

        yield 'without filtering' => [
            [],
            $expectedStdout,
            '',
        ];

        $directory = self::FIXTURES_DIR . DIRECTORY_SEPARATOR . 'SubDir';
        $expectedFilteredStdout = "SubDir/File3.php\n";

        yield 'absolute directory without a trailing separator' => [
            [$directory],
            $expectedFilteredStdout,
            '',
        ];

        if (OperatingSystem::isWindows()) {
            yield 'absolute directory with a trailing backslash' => [
                [$directory . '\\'],
                $expectedFilteredStdout,
                '',
            ];

            return;
        }

        yield 'absolute directory with a trailing slash' => [
            [$directory . '/'],
            $expectedFilteredStdout,
            '',
        ];
    }

    public function test_it_lists_filtered_source_files_and_warns_about_the_deprecated_filter_option(): void
    {
        $tester = $this->runCommand(['--filter' => 'File1']);

        $actual = $tester->getDisplay();

        $this->assertSame(
            "File1.php\n",
            Str::toUnixLineEndings($actual),
            'Stdout should contain only the selected source paths.',
        );
        $this->assertStringContainsString(
            'The "--filter" option is deprecated',
            $tester->getErrorOutput(normalize: true),
            'The deprecation warning should be written to stderr.',
        );
        $tester->assertCommandIsSuccessful();
    }

    /**
     * @param array<string, string|list<string>> $input
     */
    private function runCommand(array $input): CommandTester
    {
        $application = new Application(Container::create());
        $tester = new CommandTester($application->find('config:list-sources'));

        $tester->execute(
            $input,
            [
                'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
                'interactive' => false,
                'capture_stderr_separately' => true,
            ],
        );

        return $tester;
    }
}
