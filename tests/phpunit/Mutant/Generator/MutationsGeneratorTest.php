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

namespace Infection\Tests\Mutant\Generator;

use Infection\Console\InfectionContainer;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\MutableFileProcessed;
use Infection\Events\MutationGeneratingFinished;
use Infection\Events\MutationGeneratingStarted;
use Infection\Exception\InvalidMutatorException;
use Infection\FileSystem\SourceFileCollector;
use Infection\Mutant\Exception\ParserException;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Mutation\FileParser;
use Infection\Mutation\NodeTraverserFactory;
use Infection\Mutator\Arithmetic\Decrement;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Number\DecrementInteger;
use Infection\Mutator\Util\MutatorConfig;
use Infection\TestFramework\Coverage\LineCodeCoverage;
use Infection\Tests\Fixtures\Files\Mutation\OneFile\OneFile;
use Infection\WrongMutator\ErrorMutator;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

final class MutationsGeneratorTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../Fixtures/Files';

    public function test_it_collects_plus_mutation(): void
    {
        $codeCoverageDataMock = $this->createMock(LineCodeCoverage::class);
        $codeCoverageDataMock->method('getAllTestsForMutation');

        $mutations = $this->createMutationGenerator($codeCoverageDataMock)->generate(false);

        $this->assertInstanceOf(Plus::class, $mutations[3]->getMutator());
    }

    public function test_it_collects_public_visibility_mutation(): void
    {
        $codeCoverageDataMock = $this->createMock(LineCodeCoverage::class);
        $codeCoverageDataMock->method('getAllTestsForMutation');

        $mutations = $this->createMutationGenerator($codeCoverageDataMock)->generate(false);

        $this->assertInstanceOf(Plus::class, $mutations[3]->getMutator());
        $this->assertInstanceOf(PublicVisibility::class, $mutations[4]->getMutator());
    }

    public function test_it_can_skip_not_covered_on_file_level(): void
    {
        $codeCoverageDataMock = $this->createMock(LineCodeCoverage::class);

        $codeCoverageDataMock->expects($this->never())->method('getAllTestsForMutation');

        $codeCoverageDataMock->expects($this->once())
            ->method('hasTests')
            ->willReturn(false);

        $mutations = $this->createMutationGenerator($codeCoverageDataMock)->generate(true);

        $this->assertCount(0, $mutations);
    }

    public function test_it_can_skip_ignored_classes(): void
    {
        $codeCoverageDataMock = $this->createMock(LineCodeCoverage::class);

        $codeCoverageDataMock->expects($this->once())
            ->method('hasTests')
            ->willReturn(true);

        $generator = $this->createMutationGenerator($codeCoverageDataMock, null, new MutatorConfig([
            'ignore' => [
                OneFile::class,
            ],
        ]));

        $mutations = $generator->generate(true);

        $this->assertCount(0, $mutations);
    }

    public function test_it_executes_only_whitelisted_mutators(): void
    {
        $generator = $this->createMutationGenerator(
            $this->createMock(LineCodeCoverage::class),
            Decrement::class
        );

        $mutations = $generator->generate(false);

        $this->assertCount(0, $mutations);
    }

    public function test_it_throws_correct_error_when_file_is_invalid(): void
    {
        $generator = $this->createMutationGenerator(
            $this->createMock(LineCodeCoverage::class),
            Decrement::class,
            null,
            [self::FIXTURES_DIR . '/InvalidFile']
        );

        $this->expectException(ParserException::class);
        $this->expectExceptionMessageRegExp('#Fixtures(/|\\\)Files(/|\\\)InvalidFile(/|\\\)InvalidFile\.php#');
        $generator->generate(false);
    }

    public function test_it_throws_correct_exception_when_mutator_is_invalid(): void
    {
        $generator = $this->createMutationGenerator(
            $this->createMock(LineCodeCoverage::class),
            ErrorMutator::class
        );

        $this->expectException(InvalidMutatorException::class);
        $this->expectExceptionMessageRegExp(
            '#Encountered an error with the "ErrorMutator" mutator in the ".+OneFile.php"' .
            ' file. This is most likely a bug in Infection, so please report this in our issue tracker.#'
        );

        $generator->generate(false);
    }

    public function test_it_dispatches_the_correct_events(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                [new MutationGeneratingStarted(1)],
                [new MutableFileProcessed()],
                [new MutationGeneratingFinished()]
            );

        $generator = new MutationsGenerator(
            (new SourceFileCollector())->collectFiles(
                [self::FIXTURES_DIR . '/Mutation/OneFile'],
                [],
                ''
            ),
            $this->createMock(LineCodeCoverage::class),
            [new Plus(new MutatorConfig([]))],
            $eventDispatcher,
            $this->getParser(),
            new NodeTraverserFactory()
        );

        $generator->generate(false);
    }

    private function createMutationGenerator(
        LineCodeCoverage $codeCoverageDataMock,
        ?string $whitelistedMutatorName = null,
        ?MutatorConfig $mutatorConfig = null,
        array $srcDirs = []
    ): MutationsGenerator {
        if ($srcDirs === []) {
            $srcDirs = [
                self::FIXTURES_DIR . '/Mutation/OneFile',
            ];
        }
        $excludedDirsOrFiles = [];

        $container = new Container();

        $mutatorConfig = $mutatorConfig ?? new MutatorConfig([]);

        $container[Plus::class] = static function () use ($mutatorConfig) {
            return new Plus($mutatorConfig);
        };

        $container[PublicVisibility::class] = static function () use ($mutatorConfig) {
            return new PublicVisibility($mutatorConfig);
        };

        $container[TrueValue::class] = static function () use ($mutatorConfig) {
            return new TrueValue($mutatorConfig);
        };

        $container[DecrementInteger::class] = static function (Container $c) use ($mutatorConfig) {
            return new DecrementInteger($mutatorConfig);
        };

        $defaultMutators = [
            $container[Plus::class],
            $container[PublicVisibility::class],
            $container[TrueValue::class],
            $container[DecrementInteger::class],
        ];

        $mutators = $whitelistedMutatorName ? [new $whitelistedMutatorName($mutatorConfig)] : $defaultMutators;

        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcherMock->expects($this->any())->method('dispatch');

        return new MutationsGenerator(
            (new SourceFileCollector())->collectFiles(
                $srcDirs,
                $excludedDirsOrFiles,
                ''
            ),
            $codeCoverageDataMock,
            $mutators,
            $eventDispatcherMock,
            InfectionContainer::create()[FileParser::class],
            new NodeTraverserFactory()
        );
    }

    private function getParser(): FileParser
    {
        return InfectionContainer::create()[FileParser::class];
    }
}
