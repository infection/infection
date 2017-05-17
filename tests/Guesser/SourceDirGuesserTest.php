<?php

declare(strict_types=1);


namespace Guesser;

use Infection\Config\Guesser\SourceDirGuesser;
use PHPUnit\Framework\TestCase;

class SourceDirGuesserTest extends TestCase
{
    public function test_it_parser_psr4()
    {
        $composerJson = <<<'CODE'
{
    "autoload": {
        "psr-4": {
            "Infection\\": "src",
            "Namespace\\": "namespace"
        }
    }
}
CODE;
        $guesser = new SourceDirGuesser(json_decode($composerJson));

        $this->assertSame(['src', 'namespace'], $guesser->guess());
    }

    public function test_it_parser_psr0()
    {
        $composerJson = <<<'CODE'
{
    "autoload": {
        "psr-0": {
            "": "src"
        }
    }
}
CODE;
        $guesser = new SourceDirGuesser(json_decode($composerJson));

        $this->assertSame(['src'], $guesser->guess());
    }
}