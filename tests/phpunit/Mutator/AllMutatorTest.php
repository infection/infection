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

use Safe\sprintf;
use Generator;
use Infection\Mutator\ProfileList;
use Infection\Mutator\Util\Mutator;
use Infection\Mutator\Util\MutatorConfig;
use Infection\Tests\Fixtures\NullMutationVisitor;
use Infection\Visitor\FullyQualifiedClassNameVisitor;
use Infection\Visitor\NotMutableIgnoreVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

final class AllMutatorTest extends TestCase
{
    /**
     * @var Parser
     */
    private static $parser;

    public static function setUpBeforeClass(): void
    {
        self::$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
    }

    /**
     * This test only proves that the mutators do not crash on more 'exotic' code.
     * It does not care whether or not the code is actually mutated, only if it does not error.
     *
     * @dataProvider provideMutatorAndCodeCases
     */
    public function test_the_mutator_does_not_crash_during_parsing(string $code, Mutator $mutator, string $fileName): void
    {
        try {
            $this->getMutationsFromCode($code, $mutator);
        } catch (Throwable $t) {
            $this->fail(sprintf(
               'Ran into an error on the "%s" mutator, while parsing the file "%s". The original error was "%s"',
               $mutator::getName(),
               $fileName,
               $t->getMessage()
            ));
        }
        $this->addToAssertionCount(1);
    }

    public function provideMutatorAndCodeCases(): Generator
    {
        foreach ($this->getCodeSamples() as $codeSample) {
            foreach (ProfileList::ALL_MUTATORS as $mutator) {
                yield [$codeSample->getContents(), new $mutator(new MutatorConfig([])), $codeSample->getFilename()];
            }
        }
    }

    /**
     * @return SplFileInfo[]|Finder
     */
    public function getCodeSamples()
    {
        return Finder::create()
            ->in(__DIR__ . '/../Fixtures/CodeSamples')
            ->name('*.php')
            ->files();
    }

    private function getMutationsFromCode(string $code, Mutator $mutator): void
    {
        $initialStatements = self::$parser->parse($code);

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new NotMutableIgnoreVisitor());
        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor(new FullyQualifiedClassNameVisitor());
        $traverser->addVisitor(new ReflectionVisitor());
        $traverser->addVisitor(new NullMutationVisitor($mutator));

        $traverser->traverse($initialStatements);
    }
}
