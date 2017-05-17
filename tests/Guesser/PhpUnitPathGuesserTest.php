<?php

declare(strict_types=1);


namespace Guesser;

use Infection\Config\Guesser\PhpUnitPathGuesser;
use PHPUnit\Framework\TestCase;

class PhpUnitPathGuesserTest extends TestCase
{
    public function test_it_parser_psr0()
    {
        $composerJson = <<<'CODE'
{
    "autoload": {
        "psr-0": {
            "": "src/", 
            "SymfonyStandard": "app/"
        }
    }
}
CODE;
        $guesser = new PhpUnitPathGuesser(json_decode($composerJson));

        $this->assertSame('app', $guesser->guess());
    }

//    public function test_it_parser_psr0()
//    {
//        $composerJson = <<<'CODE'
//{
//    "autoload": {
//        "psr-0": {
//            "": "src"
//        }
//    }
//}
//CODE;
//        $guesser = new SourceDirGuesser(json_decode($composerJson));
//
//        $this->assertSame(['src'], $guesser->guess());
//    }
}