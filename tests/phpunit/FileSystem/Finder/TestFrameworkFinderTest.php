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

use function explode;
use function getenv;
use Infection\FileSystem\Finder\ComposerExecutableFinder;
use Infection\FileSystem\Finder\Exception\FinderException;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\Framework\OperatingSystem;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Tests\EnvVariableManipulation\BacksUpEnvironmentVariables;
use Infection\Tests\FileSystem\FileSystemTestCase;
use const PATH_SEPARATOR;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use function Safe\chdir;
use function Safe\putenv;
use function Safe\realpath;
use function sprintf;
use function strlen;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * @see MockVendor
 * Requires I/O read & writes via the MockVendor
 */
#[Group('integration')]
#[CoversClass(TestFrameworkFinder::class)]
final class TestFrameworkFinderTest extends FileSystemTestCase
{
    use BacksUpEnvironmentVariables;

    private static string $pathName;

    private Filesystem $fileSystem;

    private MockObject $composerFinder;

    /**
     * Saves the current environment
     */
    public static function setUpBeforeClass(): void
    {
        self::$pathName = getenv('PATH') ? 'PATH' : 'Path';
    }

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();

        parent::setUp();

        // This test relies on the current working directory to be the project
        // root.
        chdir(__DIR__ . '/../../../../');

        $this->fileSystem = new Filesystem();

        $this->composerFinder = $this->createMock(ComposerExecutableFinder::class);
        $this->composerFinder->method('find')
            ->willReturn('/usr/bin/composer');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->restoreEnvironmentVariables();
    }

    public function test_it_can_load_a_custom_path(): void
    {
        $filename = $this->fileSystem->tempnam($this->tmp, 'test');

        $frameworkFinder = new TestFrameworkFinder($this->composerFinder);

        $this->assertSame($filename, $frameworkFinder->find('not-used', $filename), 'Should return the custom path');
    }

    public function test_invalid_custom_path_throws_exception(): void
    {
        $filename = $this->fileSystem->tempnam($this->tmp, 'test');
        // Remove it so that the file doesn't exist
        $this->fileSystem->remove($filename);

        $frameworkFinder = new TestFrameworkFinder($this->composerFinder);

        $this->expectException(FinderException::class);
        $this->expectExceptionMessage('custom path');

        $frameworkFinder->find('not-used', $filename);
    }

    public function test_it_adds_vendor_bin_to_path_if_needed(): void
    {
        $path = getenv(self::$pathName);

        $frameworkFinder = new TestFrameworkFinder($this->composerFinder);

        if (OperatingSystem::isWindows()) {
            // The main script must be found from the .bat file
            $expected = realpath('vendor/phpunit/phpunit/phpunit');
        } else {
            $expected = realpath('vendor/bin/phpunit');
        }

        $this->assertSame(
            Path::normalize($expected),
            Path::normalize($frameworkFinder->find(TestFrameworkTypes::PHPUNIT)),
            'Should return the phpunit path',
        );

        $pathAfterTest = getenv(self::$pathName);

        // Vendor bin should be the first item
        $pathList = explode(PATH_SEPARATOR, $pathAfterTest);
        $this->assertStringContainsString('vendor', $pathList[0]);

        $this->assertNotSame($path, $pathAfterTest);

        $this->assertGreaterThan(
            strlen($path),
            strlen($pathAfterTest),
            'PATH with vendor added is shorter than without it added, make sure it isn\'t overwritten.',
        );
    }

    public function test_it_finds_framework_executable(): void
    {
        $mock = new MockVendor($this->tmp, $this->fileSystem);
        $mock->setUpPlatformTest();

        // Set the path to a single directory (vendor/bin)
        putenv(sprintf('%s=%s', self::$pathName, $mock->getVendorBinDir()));
        putenv('PATHEXT=');

        $frameworkFinder = new TestFrameworkFinder($this->composerFinder);

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
        putenv(sprintf('%s=%s', self::$pathName, $mock->getVendorBinDir()));
        putenv('PATHEXT=');

        $frameworkFinder = new TestFrameworkFinder($this->composerFinder);

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
}
