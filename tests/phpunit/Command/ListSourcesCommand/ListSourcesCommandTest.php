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
use InvalidArgumentException;
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

        $actualStdout = Str::toUnixLineEndings($tester->getDisplay());
        $actualStderr = $tester->getErrorOutput(normalize: true);

        $this->assertSame($expectedStdout, $actualStdout);
        $this->assertSame($expectedStderr, $actualStderr);
        $tester->assertCommandIsSuccessful();
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

        yield 'ambiguous short name' => [
            ['File'],
            $expectedStdout,
            '',
        ];

        yield 'method selector and function names with a source path' => [
            [
                'File1.php',
                'SomeClass::method',
                'calculateInvoiceTotal',
                'App\Billing\calculateInvoiceTotal',
                '\calculateInvoiceTotal',
                '\App\Billing\calculateInvoiceTotal',
            ],
            "File1.php\n",
            '',
        ];

        yield 'method selector and qualified function names with a test path' => [
            [
                '../ListSourcesCommandTest.php',
                'SomeClass::method',
                'App\Billing\calculateInvoiceTotal',
                '\calculateInvoiceTotal',
                '\App\Billing\calculateInvoiceTotal',
            ],
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

        yield 'explicit path fragment' => [
            ['Fixtures/SubDir/'],
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

        $expectedStdout = "File1.php\n";
        $expectedStderr = 'The "--filter" option is deprecated';

        $actualStdout = Str::toUnixLineEndings($tester->getDisplay());
        $actualStderr = $tester->getErrorOutput(normalize: true);

        $this->assertSame($expectedStdout, $actualStdout);
        $this->assertStringContainsString($expectedStderr, $actualStderr);
        $tester->assertCommandIsSuccessful();
    }

    /**
     * @param list<string> $paths
     */
    #[DataProvider('symbolSelectorsProvider')]
    public function test_it_rejects_source_symbol_selectors(array $paths): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Source symbol selectors do not affect which source files are listed and cannot be used on their own with config:list-sources. They are accepted alongside source or test paths so the same arguments can be reused from the run command.',
            ),
        );

        $this->runCommand(['paths' => $paths]);
    }

    public static function symbolSelectorsProvider(): iterable
    {
        yield 'namespace prefix selector' => [
            ['SomeClass\\'],
        ];

        yield 'method selector alone' => [
            ['SomeClass::method'],
        ];

        yield 'namespaced function name alone' => [
            ['App\Billing\calculateInvoiceTotal'],
        ];

        yield 'explicit global function name alone' => [
            ['\calculateInvoiceTotal'],
        ];

        yield 'fully qualified function name alone' => [
            ['\App\Billing\calculateInvoiceTotal'],
        ];
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
