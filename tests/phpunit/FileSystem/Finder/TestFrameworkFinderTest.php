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

namespace Infection\Tests\FileSystem\Finder;

use function getenv;
use Infection\FileSystem\FileSystem;
use Infection\FileSystem\Finder\ComposerBinExecutableFinder;
use Infection\FileSystem\Finder\ComposerExecutableFinder;
use Infection\FileSystem\Finder\ConcreteComposerExecutableFinder;
use Infection\FileSystem\Finder\Exception\FinderException;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\Framework\OperatingSystem;
use Infection\Process\SymfonyProcessShellCommandRunner;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use PHPUnit\Framework\MockObject\Stub;
use RuntimeException;
use function Safe\chdir;
use function Safe\chmod;
use function Safe\mkdir;
use function Safe\putenv;
use function sprintf;
use Symfony\Component\Filesystem\Path;

/**
 * @see MockVendor
 * Requires I/O read & writes via the MockVendor
 */
#[Group('integration')]
#[CoversClass(TestFrameworkFinder::class)]
#[WithEnvironmentVariable('PATH', '')]
#[WithEnvironmentVariable('PATHEXT')]
final class TestFrameworkFinderTest extends FileSystemTestCase
{
    private const string PATH_NAME = 'PATH';

    private ComposerBinExecutableFinder $composerBinExecutableFinder;

    private FileSystem $fileSystem;

    private ComposerExecutableFinder&Stub $composerFinder;

    private ShellCommandRunner $shellCommandRunner;

    protected function setUp(): void
    {
        parent::setUp();

        // This test relies on the current working directory to be the project
        // root.
        chdir(__DIR__ . '/../../../../');

        $this->fileSystem = new FileSystem();

        $this->composerBinExecutableFinder = new ComposerBinExecutableFinder();

        $this->composerFinder = $this->createStub(ComposerExecutableFinder::class);
        $this->composerFinder->method('find')
            ->willReturn(['/usr/bin/composer'])
        ;

        $this->shellCommandRunner = new SymfonyProcessShellCommandRunner();
    }

    public function test_it_can_load_a_custom_path(): void
    {
        $filename = $this->fileSystem->tempnam($this->tmp, 'test');

        $frameworkFinder = new TestFrameworkFinder(
            $this->composerFinder,
            $this->shellCommandRunner,
            $this->composerBinExecutableFinder,
            $this->fileSystem,
        );

        $this->assertSame($filename, $frameworkFinder->find('not-used', $filename), 'Should return the custom path');
    }

    public function test_invalid_custom_path_throws_exception(): void
    {
        $filename = $this->fileSystem->tempnam($this->tmp, 'test');
        // Remove it so that the file doesn't exist
        $this->fileSystem->remove($filename);

        $frameworkFinder = new TestFrameworkFinder(
            $this->composerFinder,
            $this->shellCommandRunner,
            $this->composerBinExecutableFinder,
            $this->fileSystem,
        );

        $this->expectException(FinderException::class);
        $this->expectExceptionMessage('custom path');

        $frameworkFinder->find('not-used', $filename);
    }

    public function test_it_prioritizes_an_extensionless_composer_proxy_over_a_path_batch_file_without_modifying_path(): void
    {
        chdir($this->tmp);

        $composerBinDir = $this->tmp . '/composer-bin';
        $composerExecutable = $this->createPhpUnitExecutableFixture($this->tmp);

        $pathBinDir = $this->tmp . '/path-bin';
        mkdir($pathBinDir);
        $this->createPhpUnitExecutableFixture($pathBinDir, '.bat');

        putenv(sprintf('%s=%s', self::PATH_NAME, $pathBinDir));
        putenv('PATHEXT=.bat');

        $path = self::getPath();

        $composerBinExecutableFinder = $this->createMock(ComposerBinExecutableFinder::class);
        $composerBinExecutableFinder
            ->expects($this->once())
            ->method('find')
            ->willReturn($composerExecutable)
        ;

        $frameworkFinder = $this->createFinderWithComposerBin(
            $composerBinDir,
            $composerBinExecutableFinder,
        );

        $this->assertSame(
            Path::normalize($composerExecutable),
            Path::normalize($frameworkFinder->find(TestFrameworkTypes::PHPUNIT)),
        );
        $this->assertSame($path, self::getPath());
    }

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function test_it_uses_path_when_composer_bin_has_no_executable_candidate(): void
    {
        chdir($this->tmp);

        $composerBinDir = $this->tmp . '/composer-bin';

        $pathBinDir = $this->tmp . '/path-bin';
        mkdir($pathBinDir);
        $pathExecutable = $this->createPhpUnitExecutableFixture($pathBinDir);

        putenv(sprintf('%s=%s', self::PATH_NAME, $pathBinDir));
        putenv('PATHEXT=');

        $path = self::getPath();

        $frameworkFinder = $this->createFinderWithComposerBin($composerBinDir);

        $this->assertSame(
            Path::normalize($pathExecutable),
            Path::normalize($frameworkFinder->find(TestFrameworkTypes::PHPUNIT)),
        );

        $this->assertSame($path, self::getPath());
    }

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function test_it_prioritizes_a_non_executable_composer_candidate_over_a_non_executable_path_candidate(): void
    {
        chdir($this->tmp);

        $composerBinDir = $this->tmp . '/composer-bin';
        mkdir($composerBinDir);
        $composerCandidate = $this->createPhpUnitExecutableFixture($composerBinDir);
        chmod($composerCandidate, 0644);

        $pathBinDir = $this->tmp . '/path-bin';
        mkdir($pathBinDir);
        chmod($this->createPhpUnitExecutableFixture($pathBinDir), 0644);

        putenv(sprintf('%s=%s', self::PATH_NAME, $pathBinDir));
        putenv('PATHEXT=');

        $path = self::getPath();

        $fileSystem = $this->createMock(FileSystem::class);
        $fileSystem
            ->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                [$composerBinDir . '/phpunit.bat', false],
                [$composerCandidate, true],
            ])
        ;

        $frameworkFinder = $this->createFinderWithComposerBin(
            $composerBinDir,
            fileSystem: $fileSystem,
        );

        $this->assertSame(
            Path::normalize($composerCandidate),
            Path::normalize($frameworkFinder->find(TestFrameworkTypes::PHPUNIT)),
        );
        $this->assertSame($path, self::getPath());
    }

    public function test_it_falls_back_to_local_vendor_bin_when_composer_command_fails(): void
    {
        chdir($this->tmp);

        $mock = new MockVendor($this->tmp, $this->fileSystem);
        $mock->setUpPlatformTest();

        putenv(sprintf('%s=%s', self::PATH_NAME, $this->tmp));
        putenv('PATHEXT=');

        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($this->once())
            ->method('mustRun')
            ->with(['/usr/bin/composer', 'config', 'bin-dir'])
            ->willThrowException(new RuntimeException())
        ;

        $frameworkFinder = new TestFrameworkFinder(
            $this->composerFinder,
            $shellCommandRunner,
            $this->composerBinExecutableFinder,
            $this->fileSystem,
        );

        if (OperatingSystem::isWindows()) {
            // This .bat has no code, so main script will not be found
            $expected = $mock->getVendorBinBat();
        } else {
            $expected = $mock->getVendorBinLink();
        }

        $this->assertSame(
            Path::canonicalize($expected),
            Path::canonicalize($frameworkFinder->find($mock::PACKAGE)),
        );

        $this->assertSame($this->tmp, self::getPath());
    }

    #[DataProvider('provideEmptyComposerBinDirCases')]
    public function test_it_resolves_the_test_framework_when_composer_bin_dir_output_is_empty(
        bool $fallbackExists,
        bool $expectedFallbackIsUsed,
    ): void {
        chdir($this->tmp);

        $fallbackComposerBinDir = $this->tmp . '/vendor/bin';
        $fallbackExecutable = $fallbackExists
            ? $this->createPhpUnitExecutableFixture($fallbackComposerBinDir)
            : null;

        // The project configured a different composer bin directory for some reason.
        $existingComposerBinDir = $this->tmp . '/existing-bin';
        $existingExecutable = $this->createPhpUnitExecutableFixture($existingComposerBinDir);

        $expected = Path::canonicalize($expectedFallbackIsUsed ? (string) $fallbackExecutable : $existingExecutable);

        putenv(sprintf('%s=%s', self::PATH_NAME, $existingComposerBinDir));
        putenv('PATHEXT=');

        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($this->once())
            ->method('mustRun')
            ->with(['/usr/bin/composer', 'config', 'bin-dir'])
            ->willReturn('')
        ;

        $frameworkFinder = new TestFrameworkFinder(
            $this->composerFinder,
            $shellCommandRunner,
            $this->composerBinExecutableFinder,
            $this->fileSystem,
        );

        $actual = Path::canonicalize($frameworkFinder->find(TestFrameworkTypes::PHPUNIT));

        $this->assertSame($expected, $actual);
    }

    public static function provideEmptyComposerBinDirCases(): iterable
    {
        yield 'fallback exists' => [
            'fallbackExists' => true,
            'expectedFallbackIsUsed' => true,
        ];

        yield 'fallback does not exist' => [
            'fallbackExists' => false,
            'expectedFallbackIsUsed' => false,
        ];
    }

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function test_it_falls_back_to_vendor_bin_when_the_composer_executable_is_a_directory(): void
    {
        chdir($this->tmp);

        $composerDirectory = $this->tmp . '/composer';
        $this->fileSystem->mkdir($composerDirectory, 0644);

        $fallbackComposerBinDir = $this->tmp . '/vendor/bin';
        $fallbackExecutable = $this->createPhpUnitExecutableFixture($fallbackComposerBinDir);

        putenv(sprintf('%s=%s', self::PATH_NAME, $this->tmp));
        putenv('PATHEXT=');

        $frameworkFinder = new TestFrameworkFinder(
            new ConcreteComposerExecutableFinder(),
            $this->shellCommandRunner,
            $this->composerBinExecutableFinder,
            $this->fileSystem,
        );

        $expected = Path::canonicalize($fallbackExecutable);
        $actual = Path::canonicalize($frameworkFinder->find(TestFrameworkTypes::PHPUNIT));

        $this->assertSame($expected, $actual);
    }

    public function test_it_finds_vendor_bin_with_a_local_composer_phar_without_modifying_path(): void
    {
        chdir($this->tmp);

        $composerBinDir = $this->createComposerExecutableFixture();

        $phpUnitPath = $this->createPhpUnitExecutableFixture($composerBinDir);

        putenv(sprintf('%s=%s', self::PATH_NAME, $this->tmp));
        putenv('PATHEXT=');

        $frameworkFinder = new TestFrameworkFinder(
            new ConcreteComposerExecutableFinder(),
            $this->shellCommandRunner,
            $this->composerBinExecutableFinder,
            $this->fileSystem,
        );

        $expected = Path::canonicalize($phpUnitPath);
        $actual = Path::canonicalize(
            $frameworkFinder->find(TestFrameworkTypes::PHPUNIT),
        );

        $this->assertSame($expected, $actual);
        $this->assertSame($this->tmp, self::getPath());
    }

    public function test_it_finds_framework_executable(): void
    {
        $mock = new MockVendor($this->tmp, $this->fileSystem);
        $mock->setUpPlatformTest();

        // Set the path to a single directory (vendor/bin)
        putenv(sprintf('%s=%s', self::PATH_NAME, $mock->getVendorBinDir()));
        putenv('PATHEXT=');

        $frameworkFinder = new TestFrameworkFinder(
            $this->composerFinder,
            $this->shellCommandRunner,
            $this->composerBinExecutableFinder,
            $this->fileSystem,
        );

        if (OperatingSystem::isWindows()) {
            // This .bat has no code, so main script will not be found
            $expected = $mock->getVendorBinBat();
        } else {
            $expected = $mock->getVendorBinLink();
        }

        $this->assertSame(
            Path::canonicalize($expected),
            Path::canonicalize($frameworkFinder->find($mock::PACKAGE)),
            'should return the vendor bin link or .bat',
        );
    }

    #[DataProvider('providesMockSetup')]
    public function test_it_finds_framework_script_from_bat(string $methodName): void
    {
        $mock = new MockVendor($this->tmp, $this->fileSystem);
        $mock->{$methodName}();

        // Set the path to a single directory (vendor/bin)
        putenv(sprintf('%s=%s', self::PATH_NAME, $mock->getVendorBinDir()));
        putenv('PATHEXT=');

        $frameworkFinder = new TestFrameworkFinder(
            $this->composerFinder,
            $this->shellCommandRunner,
            $this->composerBinExecutableFinder,
            $this->fileSystem,
        );

        $this->assertSame(
            Path::canonicalize($mock->getPackageScript()),
            Path::canonicalize($frameworkFinder->find($mock::PACKAGE)),
            'should return the package script from .bat',
        );
    }

    public static function providesMockSetup(): iterable
    {
        yield 'composer-bat' => ['setUpComposerBatchTest'];

        yield 'project-bat' => ['setUpProjectBatchTest'];
    }

    private static function getPath(): string
    {
        $path = getenv(self::PATH_NAME);

        return $path === false ? '' : $path;
    }

    private function createFinderWithComposerBin(
        string $composerBinDir,
        ?ComposerBinExecutableFinder $composerBinExecutableFinder = null,
        ?FileSystem $fileSystem = null,
    ): TestFrameworkFinder {
        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($this->once())
            ->method('mustRun')
            ->with(['/usr/bin/composer', 'config', 'bin-dir'])
            ->willReturn($composerBinDir)
        ;

        return new TestFrameworkFinder(
            $this->composerFinder,
            $shellCommandRunner,
            $composerBinExecutableFinder ?? $this->composerBinExecutableFinder,
            $fileSystem ?? $this->fileSystem,
        );
    }

    private function createComposerExecutableFixture(): string
    {
        $composerBinDir = $this->tmp . '/composer-bin';
        mkdir($composerBinDir);

        $this->fileSystem->dumpFile(
            $this->tmp . '/composer.phar',
            <<<'PHP'
                #!/usr/bin/env php
                <?php

                if (($argv[1] ?? null) === 'config' && ($argv[2] ?? null) === 'bin-dir') {
                    echo __DIR__ . '/composer-bin';

                    exit(0);
                }

                fwrite(STDERR, 'Unexpected Composer command: ' . implode(' ', $argv));

                exit(1);
                PHP,
        );
        chmod($this->tmp . '/composer.phar', 0755);

        return $composerBinDir;
    }

    private function createPhpUnitExecutableFixture(string $composerBinDir, string $suffix = ''): string
    {
        $phpUnitPath = $composerBinDir . '/phpunit' . $suffix;
        $this->fileSystem->dumpFile($phpUnitPath, '#!/usr/bin/env php');
        chmod($phpUnitPath, 0755);

        return $phpUnitPath;
    }
}
