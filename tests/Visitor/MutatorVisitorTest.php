<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Visitor;

use Infection\Mutation;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Util\MutatorConfig;
use Infection\Visitor\MutatorVisitor;
use PhpParser\Lexer;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

class MutatorVisitorTest extends TestCase
{
    /**
     * @dataProvider providesMutationCases
     */
    public function test_it_mutates_the_correct_node(array $inputAst, string $outputCode, Mutation $mutation)
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
        $lexer = new Lexer([
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
    protected function bye() : string
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
                true,
                true
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
                true,
                true
            ),
        ];
        $badLexer = new Lexer([
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
                true,
                true
            ),
        ];
    }
}
