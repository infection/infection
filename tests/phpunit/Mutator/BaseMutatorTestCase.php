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

namespace Infection\Tests\Mutator;

use function array_shift;
use function count;
use function escapeshellarg;
use function exec;
use function get_class;
use Infection\Mutation\Mutation;
use Infection\Mutator\Mutator;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\Visitor\CloneVisitor;
use Infection\PhpParser\Visitor\MutatorVisitor;
use Infection\Tests\AutoReview\SourceTestClassNameScheme;
use Infection\Tests\Fixtures\SimpleMutationsCollectorVisitor;
use Infection\Tests\SingletonContainer;
use Infection\Tests\StringNormalizer;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\TestCase;
use function Safe\sprintf;
use Webmozart\Assert\Assert;

abstract class BaseMutatorTestCase extends TestCase
{
    /**
     * @var Mutator
     */
    protected $mutator;

    protected function setUp(): void
    {
        // TODO: refactor this bit...
        $this->mutator = $this->createMutator();
    }

    /**
     * @var string|string[]
     * @var mixed[] $settings
     */
    final public function doTest(string $inputCode, $expectedCode = [], array $settings = []): void
    {
        $expectedCodeSamples = (array) $expectedCode;

        $inputCode = StringNormalizer::normalizeString($inputCode);

        if ($inputCode === $expectedCode) {
            $this->fail('Input code cant be the same as mutated code');
        }

        $mutatedCodes = $this->mutate($inputCode, $settings);

        $this->assertCount(
            count($mutatedCodes),
            $expectedCodeSamples,
            sprintf(
                'Failed asserting that the number of code samples (%d) equals the number of mutations (%d) created by the mutator.',
                count($expectedCodeSamples),
                count($mutatedCodes)
            )
        );

        foreach ($mutatedCodes as $realMutatedCode) {
            /** @var string|null $expectedCodeSample */
            $expectedCodeSample = array_shift($expectedCodeSamples);

            if ($expectedCodeSample === null) {
                $this->fail('The number of expected mutated code samples must equal the number of generated Mutants by mutator.');
            }

            Assert::string($expectedCodeSample);

            $this->assertSame(
                StringNormalizer::normalizeString($expectedCodeSample),
                StringNormalizer::normalizeString($realMutatedCode)
            );
            $this->assertSyntaxIsValid($realMutatedCode);
        }
    }

    final protected function createMutator(array $settings = []): Mutator
    {
        $mutatorClassName = SourceTestClassNameScheme::getSourceClassName(get_class($this));

        // TODO: this is a bit ridicule...
        return SingletonContainer::getContainer()
            ->getMutatorFactory()
            ->create([
                $mutatorClassName => ['settings' => $settings],
            ])[MutatorName::getName($mutatorClassName)]
        ;
    }

    /**
     * @return string[]
     */
    final protected function mutate(string $code, array $settings = []): array
    {
        $mutations = $this->getMutationsFromCode($code, $settings);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloneVisitor());

        $mutatedCodes = [];

        foreach ($mutations as $mutation) {
            $mutatorVisitor = new MutatorVisitor(
                $mutation->getAttributes(),
                $mutation->getMutatedNodeClass(),
                $mutation->getMutatedNode()
            );

            $traverser->addVisitor($mutatorVisitor);

            $mutatedStatements = $traverser->traverse($mutation->getOriginalFileAst());

            $mutatedCodes[] = SingletonContainer::getPrinter()->prettyPrintFile($mutatedStatements);

            $traverser->removeVisitor($mutatorVisitor);
        }

        return $mutatedCodes;
    }

    /**
     * @return Mutation[]
     */
    private function getMutationsFromCode(string $code, array $settings): array
    {
        $nodes = SingletonContainer::getContainer()->getParser()->parse($code);

        $mutationsCollectorVisitor = new SimpleMutationsCollectorVisitor(
            $this->createMutator($settings),
            $nodes
        );

        (new NodeTraverserFactory())
            ->create($mutationsCollectorVisitor, [])
            ->traverse($nodes)
        ;

        return $mutationsCollectorVisitor->getMutations();
    }

    private function assertSyntaxIsValid(string $realMutatedCode): void
    {
        exec(
            sprintf('echo %s | php -l', escapeshellarg($realMutatedCode)),
            $output,
            $returnCode
        );

        $this->assertSame(
            0,
            $returnCode,
            sprintf(
                'Mutator %s produces invalid code',
                $this->createMutator()->getName()
            )
        );
    }
}
