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

namespace Infection\Tests\Mutant;

use Infection\Mutant\MutantCodePrinter;
use Infection\Mutation\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\PhpParser\MutatedNode;
use Infection\Testing\MutatorName;
use PhpParser\Node;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

#[CoversClass(MutantCodePrinter::class)]
final class MutantCodePrinterTest extends TestCase
{
    private MutantCodePrinter $printer;

    protected function setUp(): void
    {
        $this->printer = new MutantCodePrinter(new Standard());
    }

    /**
     * @param Node[] $statements
     */
    #[DataProvider('statementsProvider')]
    public function test_it_prints_mutated_code_using_injected_printer(
        array $statements,
        Mutation $mutation,
        string $expectedCode,
    ): void {
        $result = $this->printer->print($statements, $mutation);

        $this->assertSame($expectedCode, $result);
    }

    public static function statementsProvider(): iterable
    {
        $parser = (new ParserFactory())->createForHostVersion();

        $code = <<<'PHP'
            <?php

            namespace Acme;

            echo 15;
            PHP;
        $statements = $parser->parse($code);
        $originalFileTokens = $parser->getTokens();

        Assert::notNull($statements);

        $mutation = new Mutation(
            '/path/to/acme/Foo.php',
            $statements,
            mutatorClass: Plus::class,
            mutatorName: MutatorName::getName(Plus::class),
            attributes: [
                'startLine' => 5,
                'startTokenPos' => 9,
                'startFilePos' => 29,
                'endLine' => 5,
                'endTokenPos' => 9,
                'endFilePos' => 30,
                'kind' => 10,
            ],
            mutatedNodeClass: Node\Scalar\Int_::class,
            mutatedNode: MutatedNode::wrap(
                new Node\Scalar\Int_(
                    15,
                    [
                        'startLine' => 5,
                        'startTokenPos' => 9,
                        'startFilePos' => 29,
                        'endLine' => 5,
                        'endTokenPos' => 9,
                        'endFilePos' => 30,
                        'kind' => 10,
                    ],
                ),
            ),
            mutationByMutatorIndex: 0,
            tests: [],
            originalFileTokens: $originalFileTokens,
            originalFileContent: $code,
        );

        yield 'basic namespace with echo' => [
            $statements,
            $mutation,
            $code,
        ];
    }
}
