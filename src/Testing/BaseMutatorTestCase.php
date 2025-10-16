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

namespace Infection\Testing;

use function array_flip;
use function array_key_exists;
use function array_shift;
use function count;
use function implode;
use Infection\Mutator\Mutator;
use Infection\Mutator\ProfileList;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\Visitor\MutatorVisitor;
use Infection\PhpParser\Visitor\NextConnectingVisitor;
use const PHP_EOL;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Throwable;
use function token_get_all;
use const TOKEN_PARSE;
use Webmozart\Assert\Assert;

abstract class BaseMutatorTestCase extends TestCase
{
    protected Mutator $mutator;

    protected function setUp(): void
    {
        $this->mutator = $this->createMutator();
    }

    /**
     * @param string|string[]|null $expectedCode
     * @param mixed[] $settings
     */
    final protected function assertMutatesInput(string $inputCode, string|array|null $expectedCode = [], array $settings = [], bool $allowInvalidCode = false): void
    {
        $expectedCodeSamples = (array) $expectedCode;

        $inputCode = StringNormalizer::normalizeString($inputCode);

        if ($inputCode === $expectedCode) {
            $this->fail('Input code cant be the same as mutated code');
        }

        $mutants = $this->mutate($inputCode, $settings);

        $this->assertCount(
            count($mutants),
            $expectedCodeSamples,
            sprintf(
                'Failed asserting that the number of code samples (%d) equals the number of mutants (%d) created by the mutator. Make sure mutator is enabled and mutates the source code. Mutants are: %s',
                count($expectedCodeSamples),
                count($mutants),
                StringNormalizer::normalizeString(implode(PHP_EOL, $mutants)),
            ),
        );

        foreach ($mutants as $realMutatedCode) {
            /** @var string|null $expectedCodeSample */
            $expectedCodeSample = array_shift($expectedCodeSamples);

            if ($expectedCodeSample === null) {
                $this->fail('The number of expected mutated code samples must equal the number of generated Mutants by mutator.');
            }

            Assert::string($expectedCodeSample);

            $this->assertSame(
                StringNormalizer::normalizeString($expectedCodeSample),
                StringNormalizer::normalizeString($realMutatedCode),
            );

            if (!$allowInvalidCode) {
                $this->assertSyntaxIsValid($realMutatedCode);
            }
        }
    }

    final protected function createMutator(array $settings = []): Mutator
    {
        $mutatorClassName = $this->getTestedMutatorClassName();

        $isBuiltinMutator = array_key_exists($mutatorClassName, array_flip(ProfileList::ALL_MUTATORS));
        $mutatorName = $isBuiltinMutator ? MutatorName::getName($mutatorClassName) : $mutatorClassName;

        return SingletonContainer::getContainer()
            ->getMutatorFactory()
            ->create([
                $mutatorClassName => ['settings' => $settings],
            ], false)[$mutatorName]
        ;
    }

    protected function getTestedMutatorClassName(): string
    {
        return SourceTestClassNameScheme::getSourceClassName(static::class);
    }

    /**
     * @return string[]
     */
    final protected function mutate(string $code, array $settings = []): array
    {
        $mutations = $this->getMutationsFromCode($code, $settings);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloningVisitor());

        $mutants = [];

        foreach ($mutations as $mutation) {
            $mutatorVisitor = new MutatorVisitor($mutation);

            $traverser->addVisitor($mutatorVisitor);

            $mutatedStatements = $traverser->traverse($mutation->getOriginalFileAst());

            $mutants[] = SingletonContainer::getPrinter()->prettyPrintFile($mutatedStatements);

            $traverser->removeVisitor($mutatorVisitor);
        }

        return $mutants;
    }

    /**
     * @return SimpleMutation[]
     */
    private function getMutationsFromCode(string $code, array $settings): array
    {
        $nodes = SingletonContainer::getContainer()->getParser()->parse($code);

        $this->assertNotNull($nodes);

        $mutationsCollectorVisitor = new SimpleMutationsCollectorVisitor(
            $this->createMutator($settings),
            $nodes,
        );

        // Pre-traverse the nodes to connect them
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NextConnectingVisitor());
        $traverser->traverse($nodes);

        (new NodeTraverserFactory())
            ->create($mutationsCollectorVisitor, [])
            ->traverse($nodes)
        ;

        return $mutationsCollectorVisitor->getMutations();
    }

    private function assertSyntaxIsValid(string $realMutatedCode): void
    {
        try {
            $tokens = token_get_all($realMutatedCode, TOKEN_PARSE);

            $this->assertTrue(
                $tokens !== [],
                sprintf(
                    'Mutator %s produces invalid code: %s',
                    $this->mutator->getName(),
                    $realMutatedCode,
                ),
            );
        } catch (Throwable $e) {
            $this->fail(
                sprintf(
                    'Mutator %s produces invalid code: %s',
                    $this->mutator->getName(),
                    $e->getMessage(),
                ),
            );
        }
    }
}
