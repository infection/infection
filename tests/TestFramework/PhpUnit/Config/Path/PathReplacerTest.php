<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config\Path;

use Infection\Finder\Locator;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use PHPUnit\Framework\TestCase;
use function Infection\Tests\normalizePath as p;

class PathReplacerTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function test_it_replaces_path_with_absolute_path($originalPath, $pathPostfix)
    {
        $projectPath = p(realpath(__DIR__ . '/../../../../Fixtures/Files/phpunit/project-path'));
        $pathReplacer = new PathReplacer(new Locator([$projectPath]));

        $dom = new \DOMDocument();
        $node = $dom->createElement('phpunit', $originalPath);
        $dom->appendChild($node);

        $pathReplacer->replaceInNode($node);

        $this->assertSame($projectPath . $pathPostfix, p($node->nodeValue));
    }

    public function pathProvider()
    {
        return [
            ['autoload.php', '/autoload.php'],
        ];
    }
}
