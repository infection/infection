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

namespace Infection\Tests\AutoReview\Makefile;

use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Infection\Tests\AutoReview\Makefile\Parser
 */
final class ParserTest extends TestCase
{
    /**
     * @dataProvider makefileContentProvider
     */
    public function test_it_can_parse_makefiles(string $content, array $expected): void
    {
        $actual = (new Parser())->parse($content);

        $this->assertSame($expected, $actual);
    }

    public function makefileContentProvider(): Generator
    {
        yield [
            <<<'MAKEFILE'
.PHONY: command
command: foo
    @echo 'Hello'

MAKEFILE
            ,
            [
                ['.PHONY', ['command']],
                ['command', ['foo']],
            ],
        ];

        yield [
            <<<'MAKEFILE'
.PHONY: command
command: foo bar $(DEP)
    @echo 'Hello'

MAKEFILE
            ,
            [
                ['.PHONY', ['command']],
                ['command', ['foo', 'bar', '$(DEP)']],
            ],
        ];

        yield [
            <<<'MAKEFILE'
.PHONY: command
command:    ## Comment
command: foo bar $(DEP)
    @echo 'Hello'

MAKEFILE
            ,
            [
                ['.PHONY', ['command']],
                ['command', ['## Comment']],
                ['command', ['foo', 'bar', '$(DEP)']],
            ],
        ];

        yield [
            <<<'MAKEFILE'
.PHONY: command
command: foo \
         bar \
         $(DEP)
    @echo 'Hello'

MAKEFILE
            ,
            [
                ['.PHONY', ['command']],
                ['command', ['foo', 'bar', '$(DEP)']],
            ],
        ];

        yield [
            <<<'MAKEFILE'
.PHONY: command1 command2 command3

.PHONY: command2
command2: foo \
         bar \
         $(DEP)
    @echo 'Hello'

.PHONY: command1
command1: foo \
         bar

.PHONY: command2
command1: foo

MAKEFILE
            ,
            [
                ['.PHONY', ['command1', 'command2', 'command3']],
                ['.PHONY', ['command2']],
                ['command2', ['foo', 'bar', '$(DEP)']],
                ['.PHONY', ['command1']],
                ['command1', ['foo', 'bar']],
                ['.PHONY', ['command2']],
                ['command1', ['foo']],
            ],
        ];

        yield [
            <<<'MAKEFILE'
.DEFAULT_GOAL := help

BOX=box

FLOCK := flock
MAKEFILE
            ,
            [],
        ];

        yield [
            <<<'MAKEFILE'
.DEFAULT_GOAL := help

BOX=box

# TODO: message

FLOCK := flock
MAKEFILE
            ,
            [],
        ];

        yield [
            <<<'MAKEFILE'
foo: bar
	@echo "foo:bar"
MAKEFILE
            ,
            [
                ['foo', ['bar']],
            ],
        ];

        yield [
            <<<'MAKEFILE'
FOO_URL="https://ex.co"
MAKEFILE
            ,
            [],
        ];
    }
}
