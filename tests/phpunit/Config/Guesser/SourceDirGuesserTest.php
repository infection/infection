<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Config\Guesser;

use Infection\Config\Guesser\SourceDirGuesser;
use PHPUnit\Framework\TestCase;

class SourceDirGuesserTest extends TestCase
{
    public function test_it_parser_psr4(): void
    {
        $composerJson = <<<'JSON'
{
    "autoload": {
        "psr-4": {
            "Infection\\": "abc",
            "Namespace\\": "namespace"
        }
    }
}
JSON;
        $guesser = new SourceDirGuesser(json_decode($composerJson));

        $this->assertSame(['abc', 'namespace'], $guesser->guess());
    }

    public function test_it_returns_only_src_if_several_are_in_psr_config(): void
    {
        $composerJson = <<<'JSON'
{
    "autoload": {
        "psr-4": {
            "Infection\\": "src",
            "Namespace\\": "namespace"
        }
    }
}
JSON;
        $guesser = new SourceDirGuesser(json_decode($composerJson));

        $this->assertSame(['src'], $guesser->guess());
    }

    public function test_it_parser_psr0(): void
    {
        $composerJson = <<<'JSON'
{
    "autoload": {
        "psr-0": {
            "": "src"
        }
    }
}
JSON;
        $guesser = new SourceDirGuesser(json_decode($composerJson));

        $this->assertSame(['src'], $guesser->guess());
    }

    public function test_it_returns_null_when_does_not_have_autoload(): void
    {
        $guesser = new SourceDirGuesser(json_decode('{}'));

        $this->assertNull($guesser->guess());
    }

    public function test_it_returns_null_when_does_not_have_psr_autoload(): void
    {
        $guesser = new SourceDirGuesser(json_decode('{"autoload": {"files": ["foo.php"] }}'));

        $this->assertNull($guesser->guess());
    }

    public function test_it_returns_only_src_if_contains_array_of_paths(): void
    {
        $guesser = new SourceDirGuesser(
            json_decode('{"autoload":{"psr-0": {"": ["src", "libs"]}}}')
        );

        $this->assertSame(['src'], $guesser->guess());
    }

    public function test_it_returns_list_if_contains_array_of_paths_without_src(): void
    {
        $guesser = new SourceDirGuesser(
            json_decode('{"autoload":{"psr-4": {"NameSpace\\//": ["sources", "libs"]}}}')
        );

        $this->assertSame(['sources', 'libs'], $guesser->guess());
    }

    public function test_it_throw_invalid_autoload_exception(): void
    {
        $guesser = new SourceDirGuesser(
            json_decode('{"autoload":{"psr-4": [{"NameSpace\\//": ["sources", "libs"]}]}}')
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('autoload section does not match the expected JSON schema');

        $guesser->guess();
    }
}
