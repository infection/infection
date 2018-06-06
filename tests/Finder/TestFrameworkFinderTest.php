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

    protected function setUp()
    {
        $this->workspace = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);

        $this->fileSystem = new Filesystem();
        $this->tmpDir = (new TmpDirectoryCreator($this->fileSystem))->createAndGet($this->workspace);
    }

    public function test_it_can_load_a_custom_path()
    {
        $filename = $this->fileSystem->tempnam($this->tmpDir, 'test');

        $frameworkFinder = new TestFrameworkFinder('not-used', $filename);

        $this->assertSame($filename, $frameworkFinder->find(), 'Should return the custom path');
    }

    public function test_invalid_custom_path_throws_exception()
    {
        $filename = $this->fileSystem->tempnam($this->tmpDir, 'test');
        // Remove it so that the file doesn't exist
        $this->fileSystem->remove($filename);

        $frameworkFinder = new TestFrameworkFinder('not-used', $filename);

        $this->expectException(FinderException::class);
        $this->expectExceptionMessageRegExp('/custom path/');

        $frameworkFinder->find();
    }

    public function test_it_adds_vendor_folder_to_path_if_needed()
    {
        $pathName = getenv('PATH') ? 'PATH' : 'Path';
        $path = getenv($pathName);

        $frameworkFinder = new TestFrameworkFinder('phpunit');

        $this->assertContains(normalizePath('vendor/bin/phpunit'), normalizePath($frameworkFinder->find()), 'Should return the custom path');
        $pathAfterTest = getenv($pathName);

        $this->assertNotSame($path, $pathAfterTest);
        $this->assertContains('vendor', $pathAfterTest);

        $this->assertGreaterThan(
            \strlen($path),
            \strlen($pathAfterTest),
            'PATH with vendor added is shorter than without it added, make sure it isn\'t overwritten.'
        );
    }

    protected function tearDown()
    {
        $this->fileSystem->remove($this->workspace);
    }
}
