<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Finder\Iterator;

use Infection\Tests\Fixtures\Autoloaded\Finder\MockRelativePathFinder;
use Infection\Tests\Fixtures\Finder\MockRealPathFinder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RealPathFilterIteratorTest extends TestCase
{
    /**
     * @dataProvider providesFinders
     *
     * @param string $finder
     * @param int $expectedFilecount
     */
    public function test_it_differs_from_relative_path(string $finder, int $expectedFilecount)
    {
        $sourceFilesFinder = new $finder(['tests/Fixtures/Files/Finder']);

        $filter = ['tests/Fixtures/Files/Finder/FirstFile.php'];
        $files = $sourceFilesFinder->setFilter($filter);

        $iterator = $files->getIterator();
        $iterator->rewind();
        $firstFile = $iterator->current();

        $this->assertCount($expectedFilecount, $files);

        if ($expectedFilecount === 1) {
            $this->assertSame('FirstFile.php', $firstFile->getFilename());
        }
    }

    public function providesFinders()
    {
        yield 'RealPathFileIterator' => [
            MockRealPathFinder::class,
            1,
        ];

        yield 'Symfony PathFileIterator' => [
            MockRelativePathFinder::class,
            0,
        ];
    }
}
