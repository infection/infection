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

namespace Infection\Tests\NewSrc\PhpParser;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use function str_replace;

final class NodeDumperTest extends TestCase
{
    #[DataProvider('provideTestDump')]
    public function test_dump($node, $dump): void
    {
        $dumper = new NodeDumper();

        $this->assertSame($this->canonicalize($dump), $this->canonicalize($dumper->dump($node)));
    }

    public static function provideTestDump()
    {
        return [
            [
                [],
                'array(
)',
            ],
            [
                ['Foo', 'Bar', 'Key' => 'FooBar'],
                'array(
    0: Foo
    1: Bar
    Key: FooBar
)',
            ],
            [
                new Node\Name(['Hallo', 'World']),
                'Name(
    name: Hallo\World
)',
            ],
            [
                new Node\Expr\Array_([
                    new Node\ArrayItem(new Node\Scalar\String_('Foo')),
                ]),
                'Expr_Array(
    items: array(
        0: ArrayItem(
            key: null
            value: Scalar_String(
                value: Foo
            )
            byRef: false
            unpack: false
        )
    )
)',
            ],
        ];
    }

    public function test_dump_with_positions(): void
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $dumper = new NodeDumper(['dumpPositions' => true]);

        $code = "<?php\n\$a = 1;\necho \$a;";
        $expected = <<<'OUT'
            array(
                0: Stmt_Expression[2:1 - 2:7](
                    expr: Expr_Assign[2:1 - 2:6](
                        var: Expr_Variable[2:1 - 2:2](
                            name: a
                        )
                        expr: Scalar_Int[2:6 - 2:6](
                            value: 1
                        )
                    )
                )
                1: Stmt_Echo[3:1 - 3:8](
                    exprs: array(
                        0: Expr_Variable[3:6 - 3:7](
                            name: a
                        )
                    )
                )
            )
            OUT;

        $stmts = $parser->parse($code);
        $dump = $dumper->dump($stmts, $code);

        $this->assertSame($this->canonicalize($expected), $this->canonicalize($dump));
    }

    public function test_error(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Can only dump nodes and arrays.');
        $dumper = new NodeDumper();
        $dumper->dump(new stdClass());
    }

    private function canonicalize($string)
    {
        return str_replace("\r\n", "\n", $string);
    }
}
