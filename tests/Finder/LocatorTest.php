<?php

declare(strict_types=1);

namespace Infection\Tests\Finder;

use Infection\Finder\Locator;
use PHPUnit\Framework\TestCase;

class LocatorTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function test_determines_real_path_to_file($fileName, $pathPostfix)
    {
        $projectPath = realpath(__DIR__ . '/../Files/phpunit/project-path');

        $locator = new Locator($projectPath);

        $path = $locator->locate($fileName);

        $this->assertSame($projectPath . $pathPostfix, $path);
    }

    public function test_handles_glob_patterns()
    {
        $projectPath = realpath(__DIR__ . '/../Files/phpunit/project-path');
        $locator = new Locator($projectPath);

        $directories = $locator->locateDirectories('*Bundle');

        $this->assertCount(2, $directories);
        $this->assertSame($projectPath . '/AnotherBundle', $directories[0]);
        $this->assertSame($projectPath . '/SomeBundle', $directories[1]);
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
}
