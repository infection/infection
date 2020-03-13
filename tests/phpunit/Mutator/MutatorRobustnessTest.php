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

use function array_values;
use Infection\Mutator\Mutator;
use Infection\Mutator\ProfileList;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\Tests\Fixtures\NullMutationVisitor;
use Infection\Tests\SingletonContainer;
use PHPUnit\Framework\TestCase;
use function Safe\ksort;
use function Safe\sprintf;
use const SORT_STRING;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

/**
 * @coversNothing
 */
final class MutatorRobustnessTest extends TestCase
{
    /**
     * @var string[][]|null
     */
    private static $files;

    /**
     * This test only proves that the mutators do not crash on more 'exotic' code. It does not care
     * whether or not the code is actually mutated, only if it does not error.
     *
     * @dataProvider mutatorWithCodeCaseProvider
     */
    public function test_the_mutator_does_not_crash_during_parsing(string $fileName, string $code, Mutator $mutator): void
    {
        try {
            $this->mutatesCode($code, $mutator);

            $this->addToAssertionCount(1);
        } catch (Throwable $throwable) {
            $this->fail(sprintf(
               'The mutator "%s" could not parse the file "%s": %s.',
               $mutator->getName(),
               $fileName,
               $throwable->getMessage()
            ));
        }
    }

    public function mutatorWithCodeCaseProvider(): iterable
    {
        $mutatorFactory = SingletonContainer::getContainer()->getMutatorFactory();

        foreach ($this->provideCodeSamples() as [$fileName, $fileContents]) {
            foreach (ProfileList::ALL_MUTATORS as $mutatorClassName) {
                $title = sprintf('[%s] %s', $mutatorClassName, $fileName);

                yield $title => [
                    $fileName,
                    $fileContents,
                    $mutatorFactory->create([$mutatorClassName => []])[MutatorName::getName($mutatorClassName)],
                ];
            }
        }
    }

    private function provideCodeSamples(): iterable
    {
        if (self::$files !== null) {
            yield from self::$files;

            return;
        }

        $finder = Finder::create()
            ->in(__DIR__ . '/../Fixtures/CodeSamples')
            ->name('*.php')
            ->files()
        ;

        $files = [];

        foreach ($finder as $fileInfo) {
            /* @var SplFileInfo $fileInfo */
            $files[$fileInfo->getFilename()] = [
                $fileInfo->getFilename(),
                $fileInfo->getContents(),
            ];
        }

        ksort($files, SORT_STRING);

        self::$files = array_values($files);

        yield from self::$files;
    }

    private function mutatesCode(string $code, Mutator $mutator): void
    {
        $initialStatements = SingletonContainer::getContainer()->getParser()->parse($code);

        (new NodeTraverserFactory())
            ->create(new NullMutationVisitor($mutator), [])
            ->traverse($initialStatements)
        ;
    }
}
