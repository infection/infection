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
    public function test_it_outputs_the_ast_of_a_file(): void
    {
        $tester = $this->createCommandTester();

        $expected = <<<'AST'
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
                        0: Stmt_Interface(
                            name: Identifier(
                                nodeId: 7
                                origNode: nodeId(7)
                            )
                            stmts: array(
                                0: Stmt_ClassMethod(
                                    name: Identifier(
                                        nodeId: 9
                                        origNode: nodeId(9)
                                    )
                                    returnType: Identifier(
                                        nodeId: 10
                                        origNode: nodeId(10)
                                    )
                                    nodeId: 8
                                    origNode: nodeId(8)
                                )
                            )
                            nodeId: 6
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
            AST;

        $tester->execute([
            'file' => __DIR__ . '/Greeter.php',
        ]);

        $actual = $tester->getDisplay();

        $tester->assertCommandIsSuccessful();
        $this->assertSame($expected, $actual);
    }

    #[DataProvider('invalidFileProvider')]
    public function test_it_rejects_invalid_files(
        string $file,
        string $expectedMessage,
    ): void {
        $tester = $this->createCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $tester->execute([
            'file' => $file,
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
