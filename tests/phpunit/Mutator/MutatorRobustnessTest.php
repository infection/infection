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

use Infection\Mutation\FileMutationGenerator;
use Infection\Mutator\Mutator;
use Infection\Mutator\ProfileList;
use Infection\Source\Matcher\NullSourceLineMatcher;
use Infection\Source\Matcher\SourceLineMatcher;
use Infection\TestFramework\Tracing\Tracer;
use Infection\Testing\MutatorName;
use Infection\Testing\SingletonContainer;
use Infection\Tests\TestFramework\Tracing\DummyTracer;
use function ksort;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use const SORT_STRING;
use SplFileInfo;
use function sprintf;
use Symfony\Component\Finder\Finder;
use Throwable;

#[CoversNothing]
final class MutatorRobustnessTest extends TestCase
{
    /**
     * @var array<string, SplFileInfo>|null
     */
    private static ?array $files = null;

    private FileMutationGenerator $fileMutationGenerator;

    protected function setUp(): void
    {
        $this->fileMutationGenerator = SingletonContainer::getContainer()
            ->cloneWithService(SourceLineMatcher::class, new NullSourceLineMatcher())
            ->cloneWithService(Tracer::class, new DummyTracer())
            ->getFileMutationGenerator();
    }

    /**
     * This test only proves that the mutators do not crash on more 'exotic' code. It does not care
     * whether or not the code is actually mutated, only if it does not error.
     */
    #[DataProvider('mutatorWithCodeCaseProvider')]
    public function test_the_mutator_does_not_crash_during_parsing(
        SplFileInfo $filePath,
        Mutator $mutator,
    ): void {
        try {
            $this->mutatesCode($filePath, $mutator);

            $this->addToAssertionCount(1);
        } catch (Throwable $throwable) {
            $this->fail(sprintf(
                'The mutator "%s" could not parse the file "%s": %s.',
                $mutator->getName(),
                $filePath,
                $throwable->getMessage(),
            ));
        }
    }

    public static function mutatorWithCodeCaseProvider(): iterable
    {
        $mutatorFactory = SingletonContainer::getContainer()->getMutatorFactory();

        foreach (self::provideCodeSamples() as $fileInfo) {
            foreach (ProfileList::ALL_MUTATORS as $mutatorClassName) {
                $title = sprintf('[%s] %s', $mutatorClassName, $fileInfo->getFilename());

                yield $title => [
                    $fileInfo,
                    $mutatorFactory->create([$mutatorClassName => []], false)[MutatorName::getName($mutatorClassName)],
                ];
            }
        }
    }

    /**
     * @return iterable<string, SplFileInfo>
     */
    private static function provideCodeSamples(): iterable
    {
        if (self::$files !== null) {
            yield from self::$files;

            return;
        }

        $finder = Finder::create()
            ->in(__DIR__ . '/../../autoloaded/mutator-code-samples')
            ->name('*.php')
            ->files()
        ;

        $files = take($finder)->toAssoc();
        ksort($files, SORT_STRING);

        self::$files = $files;

        yield from self::$files;
    }

    private function mutatesCode(SplFileInfo $fileInfo, Mutator $mutator): void
    {
        $mutations = $this->fileMutationGenerator->generate(
            sourceFile: $fileInfo,
            onlyCovered: false,
            mutators: [$mutator],
        );

        take($mutations)->toList();
    }
}
