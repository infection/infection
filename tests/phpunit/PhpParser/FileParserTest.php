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

use Infection\FileSystem\FileStore;
use Infection\FileSystem\FileSystem;
use Infection\Framework\Str;
use Infection\PhpParser\FileParser;
use Infection\PhpParser\UnparsableFile;
use Infection\Testing\SingletonContainer;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeDumper;
use PhpParser\Parser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception as PhpUnitFrameworkException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;
use function sprintf;

#[Group('integration')]
#[CoversClass(FileParser::class)]
final class FileParserTest extends TestCase
{
    private FileSystem&MockObject $fileSystemMock;

    private FileParser $parser;

    private NodeDumper $nodeDumper;

    protected function setUp(): void
    {
        $this->fileSystemMock = $this->createMock(FileSystem::class);

        $this->parser = new FileParser(
            SingletonContainer::getContainer()->getParser(),
            new FileStore($this->fileSystemMock),
        );

        $this->nodeDumper = SingletonContainer::getNodeDumper();
    }

    public function test_it_parses_the_given_file_only_once(): void
    {
        $fileInfo = $this->createFileInfo(
            '/unknown',
            $fileContents = 'contents',
        );

        $phpParserMock = $this->createMock(Parser::class);
        $phpParserMock
            ->expects($this->once())
            ->method('parse')
            ->with($fileContents)
            ->willReturn($expectedReturnedStatements = []);

        $phpParserMock
            ->expects($this->once())
            ->method('getTokens')
            ->willReturn($expectedReturnedTokens = []);

        $parser = new FileParser(
            $phpParserMock,
            new FileStore($this->fileSystemMock),
        );

        [$returnedStatements, $returnedTokens] = $parser->parse($fileInfo);

        $this->assertSame($expectedReturnedStatements, $returnedStatements);
        $this->assertSame($expectedReturnedTokens, $returnedTokens);
    }

    #[DataProvider('fileToParserProvider')]
    public function test_it_can_parse_a_file(
        string $contents,
        string $expectedPrintedParsedContents,
    ): void {
        $fileInfo = $this->createFileInfo('/unknown', $contents);

        [$statements] = $this->parser->parse($fileInfo);

        $this->assertContainsOnlyInstancesOf(Node::class, $statements);

        $actualPrintedParsedContents = $this->nodeDumper->dump($statements);

        $this->assertSame(
            $expectedPrintedParsedContents,
            Str::rTrimLines($actualPrintedParsedContents),
        );
    }

    public function test_it_throws_upon_failure(): void
    {
        $contents = '<?php use foo as self;';

        try {
            $this->parser->parse(
                $this->createFileInfo(
                    '/unknown',
                    $contents,
                ),
            );

            $this->fail('Expected PHPParser to be unable to parse the above expression');
        } catch (UnparsableFile $exception) {
            $this->assertSame(
                'Could not parse the file "/unknown". Check if it is a valid PHP file',
                $exception->getMessage(),
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(Error::class, $exception->getPrevious());
        }

        $fileRealPath = realpath(__FILE__);
        // Sanity check
        $this->assertNotFalse($fileRealPath);

        try {
            $this->parser->parse(
                $this->createFileInfo(
                    $fileRealPath,
                    $contents,
                ),
            );

            $this->fail('Expected PHPParser to be unable to parse the above expression');
        } catch (UnparsableFile $exception) {
            // @phpstan-ignore instanceof.internalClass
            if ($exception->getPrevious() instanceof PhpUnitFrameworkException) {
                throw $exception->getPrevious();
            }

            $this->assertSame(
                sprintf(
                    'Could not parse the file "%s". Check if it is a valid PHP file',
                    $fileRealPath,
                ),
                $exception->getMessage(),
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertInstanceOf(Error::class, $exception->getPrevious());
        }
    }

    public static function fileToParserProvider(): iterable
    {
        yield 'empty file' => [
            '',
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'empty PHP file' => [
            <<<'PHP'
                <?php

                PHP,
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'nominal' => [
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

                PHP,
            <<<'AST'
                array(
                    0: Stmt_InlineHTML(
                        value: #!/usr/bin/env php

                    )
                    1: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    name: strict_types
                                )
                                value: Scalar_Int(
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
                                        name: getcwd
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
                            0: UseItem(
                                type: TYPE_UNKNOWN (0)
                                name: Name(
                                    name: Infection\Console\Application
                                )
                                alias: null
                            )
                        )
                    )
                    4: Stmt_Use(
                        type: TYPE_NORMAL (1)
                        uses: array(
                            0: UseItem(
                                type: TYPE_UNKNOWN (0)
                                name: Name(
                                    name: Infection\Console\InfectionContainer
                                )
                                alias: null
                            )
                        )
                    )
                    5: Stmt_Expression(
                        expr: Expr_MethodCall(
                            var: Expr_New(
                                class: Name(
                                    name: Application
                                )
                                args: array(
                                    0: Arg(
                                        name: null
                                        value: Expr_StaticCall(
                                            class: Name(
                                                name: InfectionContainer
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
                AST,
        ];
    }

    private function createFileInfo(string $path, string $contents): MockSplFileInfo
    {
        $fileInfo = new MockSplFileInfo(realPath: $path);

        $this->fileSystemMock
            ->method('readFile')
            ->willReturn($contents);

        return $fileInfo;
    }
}
