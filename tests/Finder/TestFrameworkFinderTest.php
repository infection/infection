<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Finder;

use Infection\Finder\Exception\FinderException;
use Infection\Finder\TestFrameworkFinder;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use function Infection\Tests\normalizePath;

/**
 * @internal
 */
final class TestFrameworkFinderTest extends TestCase
{
    /**
     * @var string
     */
    private static $pathName;

    /**
     * @var array
     */
    private static $env;

    /**
     * @var array
     */
    private static $names;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $workspace;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * Saves the current environment
     */
    public static function setUpBeforeClass(): void
    {
        self::$pathName = getenv('PATH') ? 'PATH' : 'Path';
        self::$env = [];
        self::$names = [self::$pathName, 'PATHEXT'];

        foreach (self::$names as $name) {
            self::$env[$name] = getenv($name);
        }
    }

    protected function setUp(): void
    {
        self::restorePathEnvironment();
        $this->workspace = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);

        $this->fileSystem = new Filesystem();
        $this->tmpDir = (new TmpDirectoryCreator($this->fileSystem))->createAndGet($this->workspace);
    }

    public function test_it_can_load_a_custom_path(): void
    {
        $filename = $this->fileSystem->tempnam($this->tmpDir, 'test');

        $frameworkFinder = new TestFrameworkFinder('not-used', $filename);

        $this->assertSame($filename, $frameworkFinder->find(), 'Should return the custom path');
    }

    public function test_invalid_custom_path_throws_exception(): void
    {
        $filename = $this->fileSystem->tempnam($this->tmpDir, 'test');
        // Remove it so that the file doesn't exist
        $this->fileSystem->remove($filename);

        $frameworkFinder = new TestFrameworkFinder('not-used', $filename);

        $this->expectException(FinderException::class);
        $this->expectExceptionMessageRegExp('/custom path/');

        $frameworkFinder->find();
    }

    public function test_it_adds_vendor_bin_to_path_if_needed(): void
    {
        $path = getenv(self::$pathName);

        $frameworkFinder = new TestFrameworkFinder(TestFrameworkTypes::PHPUNIT);

        if ('\\' === \DIRECTORY_SEPARATOR) {
            // The main script must be found from the .bat file
            $expected = realpath('vendor/phpunit/phpunit/phpunit');
        } else {
            $expected = realpath('vendor/bin/phpunit');
        }

        $this->assertSame(
            normalizePath($expected),
            normalizePath($frameworkFinder->find()),
            'Should return the phpunit path'
        );

        $pathAfterTest = getenv(self::$pathName);

        // Vendor bin should be the first item
        $pathList = explode(PATH_SEPARATOR, $pathAfterTest);
        $this->assertContains('vendor', $pathList[0]);

        $this->assertNotSame($path, $pathAfterTest);

        $this->assertGreaterThan(
            \strlen($path),
            \strlen($pathAfterTest),
            'PATH with vendor added is shorter than without it added, make sure it isn\'t overwritten.'
        );
    }

    public function test_it_finds_framework_executable(): void
    {
        $mock = new MockVendor($this->tmpDir, $this->fileSystem);
        $mock->setUpPlatformTest();

        // Set the path to a single directory (vendor/bin)
        putenv(sprintf('%s=%s', self::$pathName, $mock->getVendorBinDir()));
        putenv('PATHEXT=');

        $frameworkFinder = new TestFrameworkFinder($mock::PACKAGE);

        if ('\\' === \DIRECTORY_SEPARATOR) {
            // This .bat has no code, so main script will not be found
            $expected = $mock->getVendorBinBat();
        } else {
            $expected = $mock->getVendorBinLink();
        }

        $this->assertSame(
            normalizePath($expected),
            normalizePath($frameworkFinder->find()),
            'should return the vendor bin link or .bat'
        );
    }

    /**
     * @dataProvider providesMockSetup
     */
    public function test_it_finds_framework_script_from_bat(string $methodName): void
    {
        $mock = new MockVendor($this->tmpDir, $this->fileSystem);
        $mock->{$methodName}();

        // Set the path to a single directory (vendor/bin)
        putenv(sprintf('%s=%s', self::$pathName, $mock->getVendorBinDir()));
        putenv('PATHEXT=');

        $frameworkFinder = new TestFrameworkFinder($mock::PACKAGE);

        $this->assertSame(
            normalizePath($mock->getPackageScript()),
            normalizePath($frameworkFinder->find()),
            'should return the package script from .bat'
        );
    }

    public function providesMockSetup(): array
    {
        return [
            'composer-bat' => ['setUpComposerBatchTest'],
            'project-bat' => ['setUpProjectBatchTest'],
        ];
    }

    private static function restorePathEnvironment(): void
    {
        foreach (self::$env as $name => $value) {
            if (false !== $value) {
                putenv($name . '=' . $value);
            } else {
                putenv($name);
            }
        }
    }

    protected function tearDown(): void
    {
        $this->fileSystem->remove($this->workspace);
    }

    public static function tearDownAfterClass(): void
    {
        self::restorePathEnvironment();
    }
}
