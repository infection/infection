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

namespace Infection\Tests\PhpParser;

use Generator;
use Infection\PhpParser\FileParser;
use Infection\PhpParser\UnparsableFile;
use Infection\Tests\SingletonContainer;
use Infection\Tests\StringNormalizer;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;
use function Safe\sprintf;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @group integration Requires some I/O operations
 */
final class FileParserTest extends TestCase
{
    public function test_it_parses_the_given_file_only_once(): void
    {
        $fileInfo = self::createFileInfo('/unknown', $fileContents = 'contents');

        $phpParser = $this->createMock(Parser::class);

        $phpParser
            ->expects($this->once())
            ->method('parse')
            ->with($fileContents)
            ->willReturn($expectedReturnedStatements = []);

        $parser = new FileParser($phpParser);

        $returnedStatements = $parser->parse($fileInfo);

        $this->assertSame($expectedReturnedStatements, $returnedStatements);
    }

    /**
     * @dataProvider fileToParserProvider
     */
    public function test_it_can_parse_a_file(SplFileInfo $fileInfo, string $expectedPrintedParsedContents): void
    {
        $statements = SingletonContainer::getContainer()->getFileParser()->parse($fileInfo);

        foreach ($statements as $statement) {
            $this->assertInstanceOf(Node::class, $statement);
        }

        $actualPrintedParsedContents = SingletonContainer::getNodeDumper()->dump($statements);

        $this->assertSame(
            $expectedPrintedParsedContents,
            StringNormalizer::normalizeString($actualPrintedParsedContents)
        );
    }

    public function test_it_throws_upon_failure(): void
    {
        $parser = SingletonContainer::getContainer()->getFileParser();

        try {
            $parser->parse(self::createFileInfo('/unknown', '<?php use foo as self;'));

            $this->fail('Expected PHPParser to be unable to parse the above expression');
        } catch (UnparsableFile $exception) {
            $this->assertSame(
                'Could not parse the file "/unknown". Check if it is a valid PHP file',
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(Error::class, $exception->getPrevious());
        }

        $fileRealPath = realpath(__FILE__);

        $this->assertNotFalse($fileRealPath);

        try {
            $parser->parse(self::createFileInfo($fileRealPath, '<?php use foo as self;'));

            $this->fail('Expected PHPParser to be unable to parse the above expression');
        } catch (UnparsableFile $exception) {
            $this->assertSame(
                sprintf(
                    'Could not parse the file "%s". Check if it is a valid PHP file',
                    $fileRealPath
                ),
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(Error::class, $exception->getPrevious());
        }
    }

    public function fileToParserProvider(): Generator
    {
        yield 'empty file' => [
            self::createFileInfo('/unknown', ''),
            <<<'AST'
array(
)
AST
            ,
        ];

        yield 'empty PHP file' => [
            self::createFileInfo(
                '/unknown',
                <<<'PHP'
<?php

PHP
            ),
            <<<'AST'
array(
)
AST
        ];

        yield 'nominal' => [
            self::createFileInfo(
                '/unknown',
                <<<'PHP'
#!/usr/bin/env php
<?php declare(strict_types=1);

/**
 * ...
 */

// Disable strict types for now: https://github.com/infection/infection/pull/720#issuecomment-506546284

$autoloaderInWorkingDirectory = getcwd() . '/vendor/autoload.php';

use Infection\Console\Application;
use Infection\Console\InfectionContainer;

(new Application(InfectionContainer::create()))->run();

PHP
            ),
            <<<'AST'
array(
    0: Stmt_InlineHTML(
        value: #!/usr/bin/env php

    )
    1: Stmt_Declare(
        declares: array(
            0: Stmt_DeclareDeclare(
                key: Identifier(
                    name: strict_types
                )
                value: Scalar_LNumber(
                    value: 1
                )
            )
        )
        stmts: null
    )
    2: Stmt_Expression(
        expr: Expr_Assign(
            var: Expr_Variable(
                name: autoloaderInWorkingDirectory
            )
            expr: Expr_BinaryOp_Concat(
                left: Expr_FuncCall(
                    name: Name(
                        parts: array(
                            0: getcwd
                        )
                    )
                    args: array(
                    )
                )
                right: Scalar_String(
                    value: /vendor/autoload.php
                )
            )
        )
    )
    3: Stmt_Use(
        type: TYPE_NORMAL (1)
        uses: array(
            0: Stmt_UseUse(
                type: TYPE_UNKNOWN (0)
                name: Name(
                    parts: array(
                        0: Infection
                        1: Console
                        2: Application
                    )
                )
                alias: null
            )
        )
    )
    4: Stmt_Use(
        type: TYPE_NORMAL (1)
        uses: array(
            0: Stmt_UseUse(
                type: TYPE_UNKNOWN (0)
                name: Name(
                    parts: array(
                        0: Infection
                        1: Console
                        2: InfectionContainer
                    )
                )
                alias: null
            )
        )
    )
    5: Stmt_Expression(
        expr: Expr_MethodCall(
            var: Expr_New(
                class: Name(
                    parts: array(
                        0: Application
                    )
                )
                args: array(
                    0: Arg(
                        value: Expr_StaticCall(
                            class: Name(
                                parts: array(
                                    0: InfectionContainer
                                )
                            )
                            name: Identifier(
                                name: create
                            )
                            args: array(
                            )
                        )
                        byRef: false
                        unpack: false
                    )
                )
            )
            name: Identifier(
                name: run
            )
            args: array(
            )
        )
    )
)
AST
        ];
    }

    private static function createFileInfo(string $path, string $contents): SplFileInfo
    {
        return new class($path, $contents) extends SplFileInfo {
            /**
             * @var string
             */
            private $contents;

            public function __construct(string $path, string $contents)
            {
                parent::__construct($path, $path, $path);

                $this->contents = $contents;
            }

            public function getContents(): string
            {
                return $this->contents;
            }
        };
    }
}
