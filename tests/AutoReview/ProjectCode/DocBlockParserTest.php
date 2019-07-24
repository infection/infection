<?php

declare(strict_types=1);

namespace Infection\Tests\AutoReview\ProjectCode;

use Generator;
use PHPUnit\Framework\TestCase;

final class DocBlockParserTest extends TestCase
{
    /**
     * @dataProvider docBlocksProvider
     */
    public function test_it_can_parse_PHP_doc_blocks(string $docBlock, string $expected): void
    {
        $actual = DocBlockParser::parse($docBlock);

        $this->assertSame($expected, $actual);
    }

    public function docBlocksProvider(): Generator
    {
        yield ['', ''];

        yield [
            <<<'PHP'
/**
 * This is a
 * multi-line
 * doc-block
 */
PHP
            ,
            <<<'TEXT'
This is a
multi-line
doc-block
TEXT
        ];

        yield [
            <<<'PHP'
/**
 * Single line doc-block
 */
PHP
            ,
            'Single line doc-block'
        ];

        yield [
            <<<'PHP'
   /**
 * This is a
 * multi-line  
   * doc-block
 * with weird indentation
    */
PHP
            ,
            <<<'TEXT'
This is a
multi-line
doc-block
with weird indentation
TEXT
        ];

        yield [
            <<<'PHP'
/** Inlined doc-block */
PHP
            ,
            'Inlined doc-block'
        ];

        yield [
            <<<'PHP'
// Comment
PHP
            ,
            'Comme' // Weird result: regular comments are not properly supported
        ];
    }
}
