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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class TestFrameworkFinderTest extends TestCase
{
    public function test_it_can_load_a_custom_path()
    {
        $fileSystem = new Filesystem();
        $filename = $fileSystem->tempnam(sys_get_temp_dir(), 'infection-test');
        $fileSystem->touch([$filename]);

        $frameworkFinder = new TestFrameworkFinder('not-used', $filename);

        static::assertEquals($filename, $frameworkFinder->find(), 'Should return the custom path');

        $fileSystem->remove([$filename]);
    }

    public function test_invalid_custom_path_throws_exception()
    {
        $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);

        $frameworkFinder = new TestFrameworkFinder('not-used', $filename);

        $this->expectException(FinderException::class);
        $this->expectExceptionMessageRegExp('/custom path/');

        $frameworkFinder->find();
    }
}
