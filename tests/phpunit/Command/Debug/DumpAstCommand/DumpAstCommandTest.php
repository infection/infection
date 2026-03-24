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

namespace Infection\Tests\Command\Debug\DumpAstCommand;

use Infection\Command\Debug\DumpAstCommand;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\Tests\FileSystem\FileSystemTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('integration')]
#[CoversClass(DumpAstCommand::class)]
final class DumpAstCommandTest extends FileSystemTestCase
{
    /**
     * @param array<string, string|null> $options
     */
    #[DataProvider('astProvider')]
    public function test_it_outputs_the_ast_of_a_file(
        string $file,
        array $options,
        string $expected,
    ): void {
        $tester = $this->createCommandTester();

        $tester->execute([
            'file' => $file,
            '--configuration' => __DIR__ . '/infection.json5',
            ...$options,
        ]);

        $actual = $tester->getDisplay();

        $tester->assertCommandIsSuccessful();
        $this->assertSame($expected, $actual);
    }

    public static function astProvider(): iterable
    {
        yield [
            __DIR__ . '/Greeter.php',
            [],
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier
                                value: Scalar_Int
                            )
                        )
                    )
                    1: Stmt_Namespace(
                        name: Name
                        stmts: array(
                            0: Stmt_Interface(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier
                                        returnType: Identifier
                                    )
                                )
                            )
                        )
                    )
                )
                AST,
        ];

        yield [
            __DIR__ . '/EchoGreeter.php',
            [],
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier
                                value: Scalar_Int
                            )
                        )
                    )
                    1: Stmt_Namespace(
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                                implements: array(
                                    0: Name
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier
                                        returnType: Identifier
                                        stmts: array(
                                            0: Stmt_Echo(
                                                exprs: array(
                                                    0: Scalar_String
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
                AST,
        ];

        yield 'with attributes' => [
            __DIR__ . '/EchoGreeter.php',
            ['--show-attributes' => null],
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    nodeId: 2
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(2)
                                )
                                value: Scalar_Int(
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(3)
                                )
                                nodeId: 1
                                parent: nodeId(0)
                                eligible: true
                                origNode: nodeId(1)
                            )
                        )
                        nodeId: 0
                        eligible: true
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            nodeId: 5
                            parent: nodeId(4)
                            eligible: true
                            origNode: nodeId(5)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 7
                                    parent: nodeId(6)
                                    eligible: true
                                    origNode: nodeId(7)
                                )
                                implements: array(
                                    0: Name(
                                        nodeId: 8
                                        resolvedName: nodeId(8)
                                        parent: nodeId(6)
                                        eligible: true
                                        origNode: nodeId(8)
                                    )
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 10
                                            parent: nodeId(9)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(9)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: greet
                                            eligible: true
                                            origNode: nodeId(10)
                                            mutationCandidate: true
                                        )
                                        returnType: Identifier(
                                            nodeId: 11
                                            parent: nodeId(9)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(9)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: greet
                                            eligible: true
                                            origNode: nodeId(11)
                                            mutationCandidate: true
                                        )
                                        stmts: array(
                                            0: Stmt_Echo(
                                                exprs: array(
                                                    0: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'Hello world!'
                                                        nodeId: 13
                                                        parent: nodeId(12)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(9)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: greet
                                                        eligible: true
                                                        origNode: nodeId(13)
                                                        mutationCandidate: true
                                                    )
                                                )
                                                nodeId: 12
                                                parent: nodeId(9)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                functionScope: nodeId(9)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: greet
                                                eligible: true
                                                origNode: nodeId(12)
                                                mutationCandidate: true
                                            )
                                        )
                                        nodeId: 9
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: greet
                                        eligible: true
                                        origNode: nodeId(9)
                                        mutationCandidate: true
                                    )
                                )
                                nodeId: 6
                                parent: nodeId(4)
                                eligible: true
                                origNode: nodeId(6)
                            )
                        )
                        kind: 1
                        nodeId: 4
                        eligible: true
                        next: nodeId(6)
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];

        yield 'with explicit no changed lines' => [
            __DIR__ . '/EchoGreeter.php',
            [
                '--changed-lines-ranges' => null,
            ],
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    startLine: 34
                                    endLine: 34
                                    nodeId: 2
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(2)
                                )
                                value: Scalar_Int(
                                    startLine: 34
                                    endLine: 34
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(3)
                                )
                                startLine: 34
                                endLine: 34
                                nodeId: 1
                                parent: nodeId(0)
                                eligible: true
                                origNode: nodeId(1)
                            )
                        )
                        startLine: 34
                        endLine: 34
                        nodeId: 0
                        eligible: true
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            startLine: 36
                            endLine: 36
                            nodeId: 5
                            parent: nodeId(4)
                            eligible: true
                            origNode: nodeId(5)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    startLine: 38
                                    endLine: 38
                                    nodeId: 7
                                    parent: nodeId(6)
                                    eligible: true
                                    origNode: nodeId(7)
                                )
                                implements: array(
                                    0: Name(
                                        startLine: 38
                                        endLine: 38
                                        nodeId: 8
                                        resolvedName: nodeId(8)
                                        parent: nodeId(6)
                                        eligible: true
                                        origNode: nodeId(8)
                                    )
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            startLine: 40
                                            endLine: 40
                                            nodeId: 10
                                            parent: nodeId(9)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(9)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: greet
                                            eligible: true
                                            origNode: nodeId(10)
                                        )
                                        returnType: Identifier(
                                            startLine: 40
                                            endLine: 40
                                            nodeId: 11
                                            parent: nodeId(9)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(9)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: greet
                                            eligible: true
                                            origNode: nodeId(11)
                                        )
                                        stmts: array(
                                            0: Stmt_Echo(
                                                exprs: array(
                                                    0: Scalar_String(
                                                        startLine: 42
                                                        endLine: 42
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'Hello world!'
                                                        nodeId: 13
                                                        parent: nodeId(12)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(9)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: greet
                                                        eligible: true
                                                        origNode: nodeId(13)
                                                    )
                                                )
                                                startLine: 42
                                                endLine: 42
                                                nodeId: 12
                                                parent: nodeId(9)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                functionScope: nodeId(9)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: greet
                                                eligible: true
                                                origNode: nodeId(12)
                                            )
                                        )
                                        startLine: 40
                                        endLine: 43
                                        nodeId: 9
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: greet
                                        eligible: true
                                        origNode: nodeId(9)
                                    )
                                )
                                startLine: 38
                                endLine: 44
                                nodeId: 6
                                parent: nodeId(4)
                                eligible: true
                                origNode: nodeId(6)
                            )
                        )
                        startLine: 36
                        endLine: 44
                        kind: 1
                        nodeId: 4
                        eligible: true
                        next: nodeId(6)
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];

        yield 'with explicit a changed line touching a mutation candidate' => [
            __DIR__ . '/EchoGreeter.php',
            [
                '--changed-lines-ranges' => '42:42,40:42',
            ],
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    startLine: 34
                                    endLine: 34
                                    nodeId: 2
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(2)
                                )
                                value: Scalar_Int(
                                    startLine: 34
                                    endLine: 34
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                    eligible: true
                                    origNode: nodeId(3)
                                )
                                startLine: 34
                                endLine: 34
                                nodeId: 1
                                parent: nodeId(0)
                                eligible: true
                                origNode: nodeId(1)
                            )
                        )
                        startLine: 34
                        endLine: 34
                        nodeId: 0
                        eligible: true
                        next: nodeId(4)
                        origNode: nodeId(0)
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            startLine: 36
                            endLine: 36
                            nodeId: 5
                            parent: nodeId(4)
                            eligible: true
                            origNode: nodeId(5)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    startLine: 38
                                    endLine: 38
                                    nodeId: 7
                                    parent: nodeId(6)
                                    eligible: true
                                    origNode: nodeId(7)
                                )
                                implements: array(
                                    0: Name(
                                        startLine: 38
                                        endLine: 38
                                        nodeId: 8
                                        resolvedName: nodeId(8)
                                        parent: nodeId(6)
                                        eligible: true
                                        origNode: nodeId(8)
                                    )
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            startLine: 40
                                            endLine: 40
                                            nodeId: 10
                                            parent: nodeId(9)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(9)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: greet
                                            eligible: true
                                            origNode: nodeId(10)
                                            mutationCandidate: true
                                        )
                                        returnType: Identifier(
                                            startLine: 40
                                            endLine: 40
                                            nodeId: 11
                                            parent: nodeId(9)
                                            isInsideFunction: true
                                            isStrictTypes: true
                                            functionScope: nodeId(9)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: greet
                                            eligible: true
                                            origNode: nodeId(11)
                                            mutationCandidate: true
                                        )
                                        stmts: array(
                                            0: Stmt_Echo(
                                                exprs: array(
                                                    0: Scalar_String(
                                                        startLine: 42
                                                        endLine: 42
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'Hello world!'
                                                        nodeId: 13
                                                        parent: nodeId(12)
                                                        isInsideFunction: true
                                                        isStrictTypes: true
                                                        functionScope: nodeId(9)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: greet
                                                        eligible: true
                                                        origNode: nodeId(13)
                                                        mutationCandidate: true
                                                    )
                                                )
                                                startLine: 42
                                                endLine: 42
                                                nodeId: 12
                                                parent: nodeId(9)
                                                isInsideFunction: true
                                                isStrictTypes: true
                                                functionScope: nodeId(9)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: greet
                                                eligible: true
                                                origNode: nodeId(12)
                                                mutationCandidate: true
                                            )
                                        )
                                        startLine: 40
                                        endLine: 43
                                        nodeId: 9
                                        parent: nodeId(6)
                                        isOnFunctionSignature: true
                                        isStrictTypes: true
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: greet
                                        eligible: true
                                        origNode: nodeId(9)
                                        mutationCandidate: true
                                    )
                                )
                                startLine: 38
                                endLine: 44
                                nodeId: 6
                                parent: nodeId(4)
                                eligible: true
                                origNode: nodeId(6)
                            )
                        )
                        startLine: 36
                        endLine: 44
                        kind: 1
                        nodeId: 4
                        eligible: true
                        next: nodeId(6)
                        origNode: nodeId(4)
                    )
                )
                AST,
        ];
    }

    /**
     * @param array<string, string|null> $options
     */
    #[DataProvider('invalidFileProvider')]
    public function test_it_rejects_invalid_files(
        string $file,
        string $expectedMessage,
        array $options = [],
    ): void {
        $tester = $this->createCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $tester->execute([
            'file' => $file,
            ...$options,
        ]);
    }

    public static function invalidFileProvider(): iterable
    {
        yield 'blank value' => [
            'file' => ' ',
            'expectedMessage' => 'Expected the argument "file" to be a file path. Got "".',
        ];

        yield 'non-existent file' => [
            'file' => 'non-existent-file',
            'expectedMessage' => 'Expected "non-existent-file" to be a readable file path.',
        ];

        yield 'invalid changed-lines-ranges format (invalid count)' => [
            'file' => __DIR__ . '/EchoGreeter.php',
            'expectedMessage' => 'Expected a range to follow the pattern "<startLineNumber>:<endLineNumber>". Got "invalid".',
            'options' => [
                '--configuration' => __DIR__ . '/infection.json5',
                '--changed-lines-ranges' => 'invalid',
            ],
        ];

        yield 'invalid changed-lines-ranges format (not integerish)' => [
            'file' => __DIR__ . '/EchoGreeter.php',
            'expectedMessage' => 'Invalid line numbers. Failed for the range "12:3.2".',
            'options' => [
                '--configuration' => __DIR__ . '/infection.json5',
                '--changed-lines-ranges' => '12:3.2',
            ],
        ];
    }

    private function createCommandTester(): CommandTester
    {
        $container = Container::create();

        $application = new Application($container);

        $command = new DumpAstCommand(
            $container->getFileSystem(),
        );
        $command->setApplication($application);

        return new CommandTester($command);
    }
}
