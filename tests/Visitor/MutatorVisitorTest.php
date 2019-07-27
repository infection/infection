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

namespace Infection\Tests\Visitor;

use Infection\Mutation;
use Infection\MutationInterface;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Util\MutatorConfig;
use Infection\Visitor\MutatorVisitor;
use PhpParser\Lexer;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutatorVisitorTest extends TestCase
{
    /**
     * @dataProvider providesMutationCases
     */
    public function test_it_mutates_the_correct_node(array $inputAst, string $outputCode, MutationInterface $mutation): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new MutatorVisitor($mutation));

        $result = $traverser->traverse($inputAst);
        $prettyPrinter = new Standard();

        $output = $prettyPrinter->prettyPrintFile($result);

        $this->assertSame($outputCode, $output);
    }

    public function providesMutationCases(): \Generator
    {
        $lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos',
            ],
        ]);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        yield 'It mutates the correct node' => [
            $inputAst = $parser->parse(<<<'PHP'
<?php

class Test
{
    public function hello() : string
    {
        return 'hello';
    }
    public function bye() : string
    {
        return 'bye';
    }
}
PHP
            ),
            <<<'PHP'
<?php

class Test
{
    public function hello() : string
    {
        return 'hello';
    }
    
}
PHP
            ,
            new Mutation(
                'file/to/path',
                $inputAst,
                new PublicVisibility(new MutatorConfig([])),
                [
                    'startTokenPos' => 29,
                    'endTokenPos' => 48,
                ],
                ClassMethod::class,
                new Nop(),
                0,
                range(29, 48)
            ),
        ];

        yield 'It does not mutate if only one of start or end position is correctly set' => [
            $inputAst = $parser->parse(<<<'PHP'
<?php

class Test
{
    public function hello() : string
    {
        return 'hello';
    }
    public function bye() : string
    {
        return 'bye';
    }
}
PHP
            ),
            <<<'PHP'
<?php

class Test
{
    public function hello() : string
    {
        return 'hello';
    }
    public function bye() : string
    {
        return 'bye';
    }
}
PHP
            ,
            new Mutation(
                'file/to/path',
                $inputAst,
                new PublicVisibility(new MutatorConfig([])),
                [
                    'startTokenPos' => 29,
                    'endTokenPos' => 50,
                ],
                ClassMethod::class,
                new Nop(),
                0,
                range(29, 50)
            ),
        ];
        $badLexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'endTokenPos', 'startFilePos', 'endFilePos',
            ],
        ]);

        $badParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $badLexer);

        yield 'It does not mutate if the parser does not contain startTokenPos' => [
            $inputAst = $badParser->parse(<<<'PHP'
<?php

class Test
{
    public function hello() : string
    {
        return 'hello';
    }
    public function bye() : string
    {
        return 'bye';
    }
}
PHP
            ),
            <<<'PHP'
<?php

class Test
{
    public function hello() : string
    {
        return 'hello';
    }
    public function bye() : string
    {
        return 'bye';
    }
}
PHP
            ,
            new Mutation(
                'file/to/path',
                $inputAst,
                new PublicVisibility(new MutatorConfig([])),
                [
                    'startTokenPos' => 29,
                    'endTokenPos' => 48,
                ],
                ClassMethod::class,
                new Nop(),
                0,
                range(29, 48)
            ),
        ];
    }
}
