<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Finder;

use Infection\Finder\Locator;
use PHPUnit\Framework\TestCase;
use function Infection\Tests\normalizePath as p;

class LocatorTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function test_determines_real_path_to_file($fileName, $pathPostfix)
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Files/phpunit/project-path');

        $locator = new Locator([$projectPath]);

        $path = $locator->locate($fileName);

        $this->assertSame(p($projectPath . $pathPostfix), p($path));
    }

    public function pathProvider()
    {
        return [
            ['autoload.php', '/autoload.php'],
            ['./autoload.php', '/autoload.php'],
            ['app/autoload2.php', '/app/autoload2.php'],
            ['app/../autoload.php', '/autoload.php'],
        ];
    }

    public function test_it_throws_an_exception_if_file_or_folder_does_not_exist()
    {
        $projectPath = __DIR__ . '/../Fixtures/Locator/FakeFolder';

        $locator = new Locator([$projectPath]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf(
            'The file/folder "autoload.php" does not exist (in: %s).',
            $projectPath
        ));

        $locator->locate('autoload.php');
    }

    public function test_it_throws_an_exception_if_file_or_folder_does_not_exist_when_absolute_path_is_given()
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Locator');

        $locator = new Locator(['']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf(
            'The file/folder "%s" does not exist.',
            $projectPath . 'autoload.php'
        ));

        $locator->locate($projectPath . 'autoload.php');
    }

    public function test_handles_glob_patterns()
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Files/phpunit/project-path');
        $locator = new Locator([$projectPath]);

        $directories = $locator->locateDirectories('*Bundle');

        $this->assertCount(2, $directories);
        $this->assertSame(p($projectPath . '/AnotherBundle'), p($directories[0]));
        $this->assertSame(p($projectPath . '/SomeBundle'), p($directories[1]));
    }

    public function test_locate_any_throws_exception_if_empty_array_provided()
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Files/phpunit/project-path');
        $locator = new Locator([$projectPath]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Files are not found');

        $locator->locateAnyOf([]);
    }

    public function test_it_returns_first_possible_file()
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Locator');
        $locator = new Locator([$projectPath]);

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

    public function test_it_returns_the_file_even_if_some_dont_exists()
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Locator');
        $locator = new Locator([$projectPath]);

        $found = $locator->locateAnyOf(['fakefile.txt', 'secondfile.txt', 'thirdfile.txt']);

        $this->assertStringEndsWith(
            'tests/Fixtures/Locator/secondfile.txt',
            p($found),
            'Found an incorrect file, orders may be broken.'
        );
    }

    public function test_it_throws_an_error_if_none_of_the_files_exist()
    {
        $projectPath = realpath(__DIR__ . '/../Fixtures/Files/phpunit/project-path');
        $locator = new Locator([$projectPath]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Files are not found');

        $locator->locateAnyOf(['thirdfile.txt', 'fourthfile.txt']);
    }
}
