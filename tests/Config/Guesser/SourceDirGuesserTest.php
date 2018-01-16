<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config\Guesser;

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
            "Infection\\": "abc",
            "Namespace\\": "namespace"
        }
    }
}
CODE;
        $guesser = new SourceDirGuesser(json_decode($composerJson));

        $this->assertSame(['abc', 'namespace'], $guesser->guess());
    }

    public function test_it_returns_only_src_if_several_are_in_psr_config()
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

        $this->assertSame(['src'], $guesser->guess());
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

    public function test_it_returns_null_when_does_not_have_autoload()
    {
        $guesser = new SourceDirGuesser(json_decode('{}'));

        $this->assertNull($guesser->guess());
    }

    public function test_it_returns_only_src_if_contains_array_of_paths()
    {
        $guesser = new SourceDirGuesser(
            json_decode('{"autoload":{"psr-0": {"": ["src", "libs"]}}}')
        );

        $this->assertSame(['src'], $guesser->guess());
    }

    public function test_it_returns_list_if_contains_array_of_paths_without_src()
    {
        $guesser = new SourceDirGuesser(
            json_decode('{"autoload":{"psr-4": {"NameSpace\\//": ["sources", "libs"]}}}')
        );

        $this->assertSame(['sources', 'libs'], $guesser->guess());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage autoload section does not match the expected JSON schema
     */
    public function test_it_throw_invalid_autoload_exception()
    {
        $guesser = new SourceDirGuesser(
            json_decode('{"autoload":{"psr-4": [{"NameSpace\\//": ["sources", "libs"]}]}}')
        );

        $guesser->guess();
    }
}
