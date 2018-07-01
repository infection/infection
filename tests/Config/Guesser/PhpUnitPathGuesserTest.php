<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config\Guesser;

use Infection\Config\Guesser\PhpUnitPathGuesser;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PhpUnitPathGuesserTest extends TestCase
{
    /**
     * @dataProvider providesJsonComposerAndLocations
     */
    public function test_it_guesses_correctly(string $composerJson, string $directory)
    {
        $guesser = new PhpUnitPathGuesser(json_decode($composerJson));

        $this->assertSame($directory, $guesser->guess());
    }

    public function providesJsonComposerAndLocations(): \Generator
    {
        yield [
            <<<'JSON'
{
    "autoload": {
        "psr-0": {
            "": "src"
        }
    }
}
JSON
            ,
            '.',
        ];

        yield [
            <<<'JSON'
{
    "autoload": {
        "psr-0": {
            "": "src/", 
            "SymfonyStandard": "app/"
        }
    }
}
JSON
            ,
            'app',
        ];

        yield [
            <<<'JSON'
{
    "autoload": {
        "psr-0": {
            "SymfonyStandard": "src/"
        }
    }
}
JSON
            ,
            '.',
        ];

        yield [
            <<<'JSON'
{
    "autoload": {
        "psr-4": {
            "App": "src/"
        }
    }
}
JSON
            ,
            '.',
        ];

        yield [
            <<<'JSON'
{
    "autoload-dev": {
        "psr-4": {
            "App": "src/"
        }
    }
}
JSON
            ,
            '.',
        ];

        yield [
            <<<'JSON'
{
    "autoload": {
        "files": {
            "": "src"
        }
    }
}
JSON
            ,
            '.',
        ];

        yield [
            <<<'JSON'
{
    "autoload": {
        "psr-0": {
            "App": "app/"
        }
    }
}
JSON
            ,
            '.',
        ];
    }
}
