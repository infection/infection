<?php

declare(strict_types=1);

namespace Infection\Tests\Makefile;

use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Infection\Tests\Makefile\Parser
 */
final class ParserTest extends TestCase
{
    /**
     * @dataProvider makefileContentProvider
     */
    public function test_it_can_parse_makefiles(string $content, array $expected): void
    {
        $actual = (new Parser())->parse($content);

        $this->assertEquals($expected, $actual);
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