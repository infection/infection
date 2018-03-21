<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config\Path;

use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use function Infection\Tests\normalizePath as p;

class PathReplacerTest extends TestCase
{
    /**
     * @var string
     */
    private $projectPath;

    protected function setUp()
    {
        $this->projectPath = p(realpath(__DIR__ . '/../../../../Fixtures/Files/phpunit/project-path'));
    }

    /**
     * @dataProvider pathProvider
     */
    public function test_it_replaces_path_with_absolute_path(string $originalPath, string $expectedPath)
    {
        $pathReplacer = new PathReplacer(new Filesystem());

        $dom = new \DOMDocument();
        $node = $dom->createElement('phpunit', $originalPath);
        $dom->appendChild($node);

        $pathReplacer->replaceInNode($node);

        $this->assertSame($expectedPath, p($node->nodeValue));
    }

    public function pathProvider(): array
    {
        return [
            ['autoload.php', $this->projectPath . '/autoload.php'],
            ['./autoload.php', $this->projectPath . '/autoload.php'],
            ['../autoload.php', $this->projectPath . '/../autoload.php'],
            ['/autoload.php', '/autoload.php'],
            ['./*Bundle', $this->projectPath . '/*Bundle'],
        ];
    }
}
