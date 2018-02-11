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

class PhpUnitPathGuesserTest extends TestCase
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
            <<<'CODE'
{
    "autoload": {
        "psr-0": {
            "": "src"
        }
    }
}
CODE
            ,
            '.',
        ];

        yield [
            <<<'CODE'
{
    "autoload": {
        "psr-0": {
            "": "src/", 
            "SymfonyStandard": "app/"
        }
    }
}
CODE
            ,
            'app',
        ];

        yield [
            <<<'CODE'
{
    "autoload": {
        "psr-0": {
            "SymfonyStandard": "src/"
        }
    }
}
CODE
            ,
            '.',
        ];

        yield [
            <<<'CODE'
{
    "autoload": {
        "psr-4": {
            "App": "src/"
        }
    }
}
CODE
            ,
            '.',
        ];

        yield [
            <<<'CODE'
{
    "autoload-dev": {
        "psr-4": {
            "App": "src/"
        }
    }
}
CODE
            ,
            '.',
        ];
        yield [
            <<<'CODE'
{
    "autoload": {
        "files": {
            "": "src"
        }
    }
}
CODE
            ,
            '.',
        ];

        yield [
            <<<'CODE'
{
    "autoload": {
        "psr-0": {
            "App": "app/"
        }
    }
}
CODE
            ,
            '.',
        ];
    }
}
