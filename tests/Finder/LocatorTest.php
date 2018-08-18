<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Finder;

use Infection\Finder\Exception\LocatorException;
use Infection\Finder\Locator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use function Infection\Tests\normalizePath as p;

/**
 * @internal
 */
final class LocatorTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @dataProvider pathProvider
     */
    public function test_determines_real_path_to_file(string $fileName, string $pathPostfix): void
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Files/phpunit/project-path');

        $locator = new Locator([$projectPath], $this->filesystem);

        $path = $locator->locate($fileName);

        $this->assertSame(p($projectPath . $pathPostfix), p($path));
    }

    public function pathProvider(): array
    {
        return [
            ['autoload.php', '/autoload.php'],
            ['./autoload.php', '/autoload.php'],
            ['app/autoload2.php', '/app/autoload2.php'],
            ['app/../autoload.php', '/autoload.php'],
            [
                realpath(__DIR__ . '/../Fixtures/Files/phpunit/project-path/autoload.php'),
                '/autoload.php',
            ],
        ];
    }

    public function test_it_throws_an_exception_if_file_or_folder_does_not_exist(): void
    {
        $projectPath = __DIR__ . '/../Fixtures/Locator/FakeFolder';

        $locator = new Locator([$projectPath], $this->filesystem);

        $this->expectException(LocatorException::class);
        $this->expectExceptionMessage(sprintf(
            'The file/folder "autoload.php" does not exist (in: %s).',
            $projectPath
        ));

        $locator->locate('autoload.php');
    }

    public function test_it_throws_an_exception_if_file_or_folder_does_not_exist_when_absolute_path_is_given(): void
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Locator');

        $locator = new Locator([''], $this->filesystem);

        $invalidPath = $projectPath . 'autoload.php';

        $this->expectException(LocatorException::class);
        $this->expectExceptionMessage(sprintf(
            'The file/directory "%s" does not exist.',
            $invalidPath
        ));

        $locator->locate($invalidPath);
    }

    public function test_locate_any_throws_exception_if_empty_array_provided(): void
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Files/phpunit/project-path');
        $locator = new Locator([$projectPath], $this->filesystem);

        $this->expectException(LocatorException::class);
        $this->expectExceptionMessage('Files are not found');

        $locator->locateAnyOf([]);
    }

    public function test_it_returns_first_possible_file(): void
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Locator');
        $locator = new Locator([$projectPath], $this->filesystem);

        $found = $locator->locateAnyOf(['file.txt', 'secondfile.txt']);
        $this->assertStringEndsWith(
            'tests/Fixtures/Locator/file.txt',
            p($found),
            'Found an incorrect file, orders may be broken.'
        );

        $found = $locator->locateAnyOf(['secondfile.txt', 'file.txt']);

        $this->assertStringEndsWith(
            'tests/Fixtures/Locator/secondfile.txt',
            p($found),
            'Found an incorrect file, orders may be broken.'
        );
    }

    public function test_it_returns_the_file_even_if_some_dont_exists(): void
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Locator');
        $locator = new Locator([$projectPath], $this->filesystem);

        $found = $locator->locateAnyOf(['fakefile.txt', 'secondfile.txt', 'thirdfile.txt']);

        $this->assertStringEndsWith(
            'tests/Fixtures/Locator/secondfile.txt',
            p($found),
            'Found an incorrect file, orders may be broken.'
        );
    }

    public function test_it_throws_an_error_if_none_of_the_files_exist(): void
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Files/phpunit/project-path');
        $locator = new Locator([$projectPath], $this->filesystem);

        $this->expectException(LocatorException::class);
        $this->expectExceptionMessage('Files are not found');

        $locator->locateAnyOf(['thirdfile.txt', 'fourthfile.txt']);
    }
}
