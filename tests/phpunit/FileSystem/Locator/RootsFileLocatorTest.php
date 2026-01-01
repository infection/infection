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

namespace Infection\Tests\FileSystem\Locator;

use Infection\FileSystem\Locator\FileNotFound;
use Infection\FileSystem\Locator\RootsFileLocator;
use Infection\Framework\OperatingSystem;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;
use function sprintf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * Requires IO reads
 */
#[Group('integration')]
#[CoversClass(RootsFileLocator::class)]
final class RootsFileLocatorTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../Fixtures/Locator';

    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @param string[] $roots
     * @param non-empty-string $fileName
     */
    #[DataProvider('pathsProvider')]
    public function test_it_can_locate_files(
        array $roots,
        string $fileName,
        string $expected,
    ): void {
        $actual = (new RootsFileLocator($roots, $this->filesystem))->locate($fileName);

        $this->assertSame(
            Path::normalize($expected),
            Path::normalize($actual),
        );
    }

    /**
     * @param string[] $roots
     * @param non-empty-string $fileName
     */
    #[DataProvider('invalidPathsProvider')]
    public function test_it_throws_an_exception_if_file_or_folder_does_not_exist(
        array $roots,
        string $fileName,
        string $expectedErrorMessage,
    ): void {
        $locator = new RootsFileLocator($roots, $this->filesystem);

        try {
            $locator->locate($fileName);

            $this->fail('Expected an exception to be thrown.');
        } catch (FileNotFound $exception) {
            $this->assertSame($expectedErrorMessage, $exception->getMessage());
            $this->assertSame(0, $exception->getCode());
            $this->assertNull($exception->getPrevious());
        }
    }

    /**
     * @param string[] $roots
     * @param non-empty-string[] $fileNames
     */
    #[DataProvider('multiplePathsProvider')]
    public function test_it_can_locate_one_of_the_given_files(
        array $roots,
        array $fileNames,
        string $expected,
    ): void {
        $actual = (new RootsFileLocator($roots, $this->filesystem))->locateOneOf($fileNames);

        $this->assertSame(
            Path::normalize($expected),
            Path::normalize($actual),
        );
    }

    /**
     * @param string[] $roots
     * @param non-empty-string[] $fileNames
     */
    #[DataProvider('multipleInvalidPathsProvider')]
    public function test_locate_any_throws_exception_if_no_file_could_be_found(
        array $roots,
        array $fileNames,
        string $expectedErrorMessage,
    ): void {
        $locator = new RootsFileLocator($roots, $this->filesystem);

        try {
            $locator->locateOneOf($fileNames);

            $this->fail('Expected an exception to be thrown.');
        } catch (FileNotFound $exception) {
            $this->assertSame(
                $expectedErrorMessage,
                $exception->getMessage(),
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertNull($exception->getPrevious());
        }
    }

    public static function pathsProvider(): iterable
    {
        $root = realpath(self::FIXTURES_DIR);

        $generators = [];

        $generators[] = static function () use ($root): iterable {
            $title = 'one root';
            $case = 'locate root file';

            $roots = [$root . '/dir'];
            $expected = $root . '/dir/root';

            $paths = [
                'root',
                './root',
                'root/',
                'sub-dir/../root',
                './sub-dir/../root',
                $root . '/dir/root',
                $root . '/dir/sub-dir/../root',
            ];

            foreach ($paths as $index => $path) {
                $name = sprintf('[%s][%s] #%s', $title, $case, $index);

                yield $name => [
                    $roots,
                    $path,
                    $expected,
                ];
            }
        };

        $generators[] = static function () use ($root): iterable {
            $title = 'one root';
            $case = 'locate sub-dir root file';

            $roots = [$root . '/dir'];
            $expected = $root . '/dir/sub-dir/sub-dir-root';

            $paths = [
                'sub-dir/sub-dir-root',
                './sub-dir/sub-dir-root',
            ];

            foreach ($paths as $index => $path) {
                $name = sprintf('[%s][%s] #%s', $title, $case, $index);

                yield $name => [
                    $roots,
                    $path,
                    $expected,
                ];
            }
        };

        $generators[] = static function () use ($root): iterable {
            $title = 'multiple roots';
            $case = 'locate root file';

            $roots = [
                $root . '/dir',
                $root . '/sub-dir',
            ];
            $expected = $root . '/dir/root';

            $paths = [
                'root',
                './root',
                'sub-dir/../root',
                './sub-dir/../root',
                $root . '/dir/root',
                $root . '/dir/sub-dir/../root',
            ];

            foreach ($paths as $index => $path) {
                $name = sprintf('[%s][%s] #%s', $title, $case, $index);

                yield $name => [
                    $roots,
                    $path,
                    $expected,
                ];
            }
        };

        $generators[] = static function () use ($root): iterable {
            $title = 'multiple roots';
            $case = 'locate sub-dir root file';

            $roots = [
                $root . '/dir',
                $root . '/sub-dir',
            ];
            $expected = $root . '/dir/sub-dir/sub-dir-root';

            $paths = [
                'sub-dir/sub-dir-root',
                './sub-dir/sub-dir-root',
            ];

            foreach ($paths as $index => $path) {
                $name = sprintf('[%s][%s] #%s', $title, $case, $index);

                yield $name => [
                    $roots,
                    $path,
                    $expected,
                ];
            }
        };

        if (!OperatingSystem::isWindows()) {
            $generators[] = static function () use ($root): iterable {
                $title = 'one root';
                $case = 'locate symlinked file';

                $roots = [$root . '/dir'];
                $expected = $root . '/dir/sub-dir/sub-dir-root';

                $paths = [
                    'sub-dir-root-symlink',
                    './sub-dir-root-symlink',
                    'sub-dir-root-symlink/',
                    './sub-dir-root-symlink/',
                ];

                foreach ($paths as $index => $path) {
                    $name = sprintf('[%s][%s] #%s', $title, $case, $index);

                    yield $name => [
                        $roots,
                        $path,
                        $expected,
                    ];
                }
            };
        }

        foreach ($generators as $generator) {
            yield from iterator_to_array($generator(), true);
        }
    }

    public static function invalidPathsProvider(): iterable
    {
        yield [
            ['/nowhere'],
            'unknown',
            'Could not locate the file "unknown" in "/nowhere".',
        ];

        yield [
            ['/nowhere'],
            '/unknown',
            'Could not locate the file "/unknown" in "/nowhere".',
        ];

        yield [
            ['/nowhere1', '/nowhere2'],
            'unknown',
            'Could not locate the file "unknown" in "/nowhere1", "/nowhere2".',
        ];

        $fixturesDir = realpath(self::FIXTURES_DIR);

        if (!OperatingSystem::isWindows()) {
            yield [
                [$fixturesDir],
                'broken-symlink',
                sprintf(
                    'Could not locate the file "broken-symlink" in "%s".',
                    $fixturesDir,
                ),
            ];
        }
    }

    public static function multiplePathsProvider(): iterable
    {
        $root = realpath(self::FIXTURES_DIR);

        yield [
            [$root . '/dir'],
            ['root'],
            $root . '/dir/root',
        ];

        yield [
            [
                $root . '/dir',
                $root . '/sub-dir',
            ],
            ['root'],
            $root . '/dir/root',
        ];

        yield [
            [
                $root . '/dir/sub-dir',
                $root . '/dir',
            ],
            ['root'],
            $root . '/dir/sub-dir/root',
        ];

        yield [
            [$root . '/dir/sub-dir'],
            [
                'root',
                'sub-dir-root',
            ],
            $root . '/dir/sub-dir/root',
        ];

        yield [
            [$root . '/dir/sub-dir'],
            [
                'sub-dir-root',
                'root',
            ],
            $root . '/dir/sub-dir/sub-dir-root',
        ];

        yield [
            [$root . '/dir/sub-dir'],
            [
                10 => 'sub-dir-root',
                'root',
            ],
            $root . '/dir/sub-dir/sub-dir-root',
        ];

        yield [
            [$root . '/dir'],
            [
                '/unknown',
                'root',
            ],
            $root . '/dir/root',
        ];
    }

    public static function multipleInvalidPathsProvider(): iterable
    {
        $root1 = realpath(self::FIXTURES_DIR);
        $root2 = realpath(self::FIXTURES_DIR) . '/dir';

        yield [
            [],
            [],
            'Could not locate any files (no file provided).',
        ];

        yield [
            [],
            ['/unknown1', '/unknown2'],
            'Could not locate the files "/unknown1", "/unknown2"',
        ];

        yield [
            [$root1],
            ['/unknown1', '/unknown2'],
            sprintf('Could not locate the files "/unknown1", "/unknown2" in "%s"', $root1),
        ];

        yield [
            [$root1, $root2],
            ['/unknown1', '/unknown2'],
            sprintf(
                'Could not locate the files "/unknown1", "/unknown2" in "%s", "%s"',
                $root1,
                $root2,
            ),
        ];
    }
}
