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
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\Git\Git;
use Infection\Tests\Configuration\Schema\SchemaConfigurationBuilder;
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
#[CoversClass(GitChangedFilesCommand::class)]
final class GitChangedFilesCommandTest extends TestCase
{
    private const REFERENCE = 'xyz1234';

    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    private const SOURCE_DIRECTORIES = ['src', 'lib'];

    private string $cwd = '';

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
     * @param array<string, string> $arguments
     */
    #[DataProvider('commandExecutionProvider')]
    public function test_it_outputs_changed_files(
        array $arguments,
        string $defaultBase,
        string $files,
        string $expectedBase,
        string $expectedFilter,
        string $expectedStdout,
        string $expectedStderr,
        string $expectedDisplay,
    ): void {
        $gitMock = $this->createMock(Git::class);
        $gitMock->method('getDefaultBase')->willReturn($defaultBase);
        $gitMock
            ->method('getBaseReference')
            ->with($expectedBase)
            ->willReturn(self::REFERENCE);
        $gitMock
            ->method('getChangedFileRelativePaths')
            ->with($expectedFilter, self::REFERENCE, ['src', 'lib'])
            ->willReturn($files);

        $tester = $this->createCommandTester($gitMock);

        $tester->execute(
            $arguments,
            [
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
                'capture_stderr_separately' => true,
            ],
        );

        $tester->assertCommandIsSuccessful();
        $this->assertSame($expectedStdout, $tester->getDisplay(normalize: true));
        $this->assertSame($expectedStderr, $tester->getErrorOutput(normalize: true));

        $tester->execute(
            $arguments,
            [
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            ],
        );

        $tester->assertCommandIsSuccessful();
        $this->assertSame($expectedDisplay, $tester->getDisplay(normalize: true));
    }

    public static function commandExecutionProvider(): iterable
    {
        yield 'provided base and default filter' => [
            [
                '--base' => 'origin/main',
            ],
            'defaultBase' => 'origin/default',
            'files' => 'src/File1.php,src/File2.php',
            'expectedBase' => 'origin/main',
            'expectedFilter' => Git::DEFAULT_GIT_DIFF_FILTER,
            'expectedStdout' => <<<STDOUT
                src/File1.php
                src/File2.php

                STDOUT,
            'expectedStderr' => <<<STDERR
                [notice] Using the reference "xyz1234".

                STDERR,
            'expectedDisplay' => <<<DISPLAY
                [notice] Using the reference "xyz1234".
                src/File1.php
                src/File2.php

                DISPLAY,
        ];

        yield 'default base and default filter' => [
            [],
            'defaultBase' => 'origin/default',
            'files' => 'tests/File1Test.php',
            'expectedBase' => 'origin/default',
            'expectedFilter' => Git::DEFAULT_GIT_DIFF_FILTER,
            'expectedStdout' => <<<STDOUT
                tests/File1Test.php

                STDOUT,
            'expectedStderr' => <<<STDERR
                [notice] No base found. Using the default base "origin/default".
                [notice] Using the reference "xyz1234".

                STDERR,
            'expectedDisplay' => <<<DISPLAY
                [notice] No base found. Using the default base "origin/default".
                [notice] Using the reference "xyz1234".
                tests/File1Test.php

                DISPLAY,
        ];

        yield 'trimmed base option' => [
            [
                '--base' => '  feature/test  ',
            ],
            'defaultBase' => 'origin/default',
            'files' => 'src/Test.php',
            'expectedBase' => 'feature/test',
            'expectedFilter' => Git::DEFAULT_GIT_DIFF_FILTER,
            'expectedStdout' => <<<STDOUT
                src/Test.php

                STDOUT,
            'expectedStderr' => <<<STDERR
                [notice] Using the reference "xyz1234".

                STDERR,
            'expectedDisplay' => <<<DISPLAY
                [notice] Using the reference "xyz1234".
                src/Test.php

                DISPLAY,
        ];

        yield 'trimmed filter option' => [
            [
                '--base' => 'origin/main',
                '--filter' => '  D  ',
            ],
            'defaultBase' => 'origin/default',
            'files' => 'src/Deleted.php',
            'expectedBase' => 'origin/main',
            'expectedFilter' => 'D',
            'expectedStdout' => <<<STDOUT
                src/Deleted.php

                STDOUT,
            'expectedStderr' => <<<STDERR
                [notice] Using the reference "xyz1234".

                STDERR,
            'expectedDisplay' => <<<DISPLAY
                [notice] Using the reference "xyz1234".
                src/Deleted.php

                DISPLAY,
        ];

        yield 'single file' => [
            [],
            'defaultBase' => 'origin/main',
            'files' => 'src/SingleFile.php',
            'expectedBase' => 'origin/main',
            'expectedFilter' => Git::DEFAULT_GIT_DIFF_FILTER,
            'expectedStdout' => <<<STDOUT
                src/SingleFile.php

                STDOUT,
            'expectedStderr' => <<<STDERR
                [notice] No base found. Using the default base "origin/main".
                [notice] Using the reference "xyz1234".

                STDERR,
            'expectedDisplay' => <<<DISPLAY
                [notice] No base found. Using the default base "origin/main".
                [notice] Using the reference "xyz1234".
                src/SingleFile.php

                DISPLAY,
        ];
    }

    public function test_it_rejects_blank_base_option(): void
    {
        $git = $this->createStub(Git::class);
        $tester = $this->createCommandTester($git);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a non-blank value for the option "--base".');

        $tester->execute(['--base' => '   ']);
    }

    public function test_it_rejects_blank_filter_option(): void
    {
        $git = $this->createStub(Git::class);
        $tester = $this->createCommandTester($git);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a non-blank value for the option "--base".');

        $tester->execute([
            '--base' => 'origin/main',
            '--filter' => '   ',
        ]);
    }

    private function createCommandTester(Git $git): CommandTester
    {
        $container = Container::create();
        $container->set(Git::class, static fn () => $git);
        $container->set(SchemaConfiguration::class, self::createSchemaConfiguration(...));

        $application = new Application($container);

        $command = new GitChangedFilesCommand();
        $command->setApplication($application);

        return new CommandTester($command);
    }

    private function createSchemaConfiguration(): SchemaConfiguration
    {
        return SchemaConfigurationBuilder::withMinimalTestData()
            ->withSource(
                new Source(
                    self::SOURCE_DIRECTORIES,
                    ['tests'],
                ),
            )
            ->build();
    }
}
