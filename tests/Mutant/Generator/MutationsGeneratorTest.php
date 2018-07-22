<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutant\Generator;

use Infection\Config\Exception\InvalidConfigException;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Exception\InvalidMutatorException;
use Infection\Mutant\Exception\ParserException;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Mutator\Arithmetic\Decrement;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Util\MutatorConfig;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Tests\Fixtures\Files\Mutation\OneFile\OneFile;
use Infection\WrongMutator\ErrorMutator;
use Mockery;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Pimple\Container;

/**
 * @internal
 */
final class MutationsGeneratorTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function test_it_collects_plus_mutation(): void
    {
        $codeCoverageDataMock = Mockery::mock(CodeCoverageData::class);
        $codeCoverageDataMock->shouldReceive('hasTestsOnLine')->once()->andReturn(true);
        $codeCoverageDataMock->shouldReceive('hasExecutedMethodOnLine')->twice()->andReturn(false);

        $generator = $this->createMutationGenerator($codeCoverageDataMock);

        $mutations = $generator->generate(false);

        $this->assertInstanceOf(Plus::class, $mutations[1]->getMutator());
    }

    public function test_it_collects_public_visibility_mutation(): void
    {
        $codeCoverageDataMock = Mockery::mock(CodeCoverageData::class);
        $codeCoverageDataMock->shouldReceive('hasTestsOnLine')->once()->andReturn(true);
        $codeCoverageDataMock->shouldReceive('hasExecutedMethodOnLine')->twice()->andReturn(true);

        $generator = $this->createMutationGenerator($codeCoverageDataMock);

        $mutations = $generator->generate(false);

        $this->assertInstanceOf(Plus::class, $mutations[1]->getMutator());
        $this->assertInstanceOf(PublicVisibility::class, $mutations[2]->getMutator());
    }

    public function test_it_can_skip_not_covered_on_file_level(): void
    {
        $codeCoverageDataMock = Mockery::mock(CodeCoverageData::class);
        $codeCoverageDataMock->shouldReceive('hasTests')->once()->andReturn(false);

        $generator = $this->createMutationGenerator($codeCoverageDataMock);

        $mutations = $generator->generate(true);

        $this->assertCount(0, $mutations);
    }

    public function test_it_can_skip_not_covered_on_file_line_level(): void
    {
        $codeCoverageDataMock = Mockery::mock(CodeCoverageData::class);
        $codeCoverageDataMock->shouldReceive('hasTests')->once()->andReturn(true);
        $codeCoverageDataMock->shouldReceive('hasTestsOnLine')->once()->andReturn(false);
        $codeCoverageDataMock->shouldReceive('hasExecutedMethodOnLine')->twice()->andReturn(true);

        $generator = $this->createMutationGenerator($codeCoverageDataMock);

        $mutations = $generator->generate(true);

        $this->assertCount(2, $mutations);
        $this->assertInstanceOf(TrueValue::class, $mutations[0]->getMutator());
        $this->assertInstanceOf(PublicVisibility::class, $mutations[1]->getMutator());
    }

    public function test_it_can_skip_not_covered_on_file_line_for_visibility(): void
    {
        $codeCoverageDataMock = Mockery::mock(CodeCoverageData::class);
        $codeCoverageDataMock->shouldReceive('hasTests')->once()->andReturn(true);
        $codeCoverageDataMock->shouldReceive('hasTestsOnLine')->once()->andReturn(false);
        $codeCoverageDataMock->shouldReceive('hasExecutedMethodOnLine')->twice()->andReturn(false);

        $generator = $this->createMutationGenerator($codeCoverageDataMock);

        $mutations = $generator->generate(true);

        $this->assertCount(0, $mutations);
    }

    public function test_it_can_skip_ignored_classes(): void
    {
        $codeCoverageDataMock = Mockery::mock(CodeCoverageData::class);
        $codeCoverageDataMock->shouldReceive('hasTests')->once()->andReturn(true);

        $generator = $this->createMutationGenerator($codeCoverageDataMock, [], new MutatorConfig([
            'ignore' => [
                OneFile::class,
            ],
        ]));

        $mutations = $generator->generate(true);

        $this->assertCount(0, $mutations);
    }

    public function test_it_executes_only_whitelisted_mutators(): void
    {
        $codeCoverageDataMock = Mockery::mock(CodeCoverageData::class);

        $generator = $this->createMutationGenerator($codeCoverageDataMock, [Decrement::getName()]);

        $mutations = $generator->generate(false);

        $this->assertCount(0, $mutations);
    }

    public function test_whitelist_is_case_sensitive(): void
    {
        $codeCoverageDataMock = Mockery::mock(CodeCoverageData::class);

        $generator = $this->createMutationGenerator($codeCoverageDataMock, [strtolower(Decrement::getName())]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('not recognized');

        $generator->generate(false);
    }

    public function test_it_throws_correct_error_when_file_is_invalid(): void
    {
        $generator = $this->createMutationGenerator(
            Mockery::mock(CodeCoverageData::class),
            [Decrement::getName()],
            null,
            [\dirname(__DIR__, 2) . '/Fixtures/Files/InvalidFile']
        );

        $this->expectException(ParserException::class);
        $this->expectExceptionMessageRegExp('#Fixtures(/|\\\)Files(/|\\\)InvalidFile(/|\\\)InvalidFile\.php#');
        $generator->generate(false);
    }

    public function test_it_throws_correct_exception_when_mutator_is_invalid(): void
    {
        $generator = $this->createMutationGenerator(
            Mockery::mock(CodeCoverageData::class),
            [ErrorMutator::class]
        );

        $this->expectException(InvalidMutatorException::class);
        $this->expectExceptionMessageRegExp(
            '#Encountered an error with the "ErrorMutator" mutator in the ".+OneFile.php"' .
            ' file. This is most likely a bug in Infection, so please report this in our issue tracker.#'
        );

        $generator->generate(false);
    }

    private function createMutationGenerator(
        CodeCoverageData $codeCoverageDataMock,
        array $whitelistedMutatorNames = [],
        MutatorConfig $mutatorConfig = null,
        array $srcDirs = []
    ) {
        if ($srcDirs === []) {
            $srcDirs = [
                \dirname(__DIR__, 2) . '/Fixtures/Files/Mutation/OneFile',
            ];
        }
        $excludedDirsOrFiles = [];

        $container = new Container();

        $mutatorConfig = $mutatorConfig ?? new MutatorConfig([]);

        $container[Plus::class] = function (Container $c) use ($mutatorConfig) {
            return new Plus($mutatorConfig);
        };

        $container[PublicVisibility::class] = function (Container $c) use ($mutatorConfig) {
            return new PublicVisibility($mutatorConfig);
        };

        $container[TrueValue::class] = function (Container $c) use ($mutatorConfig) {
            return new TrueValue($mutatorConfig);
        };

        $defaultMutators = [
            $container[Plus::class],
            $container[PublicVisibility::class],
            $container[TrueValue::class],
        ];

        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcherMock->expects($this->any())->method('dispatch');

        return new MutationsGenerator(
            $srcDirs,
            $excludedDirsOrFiles,
            $codeCoverageDataMock,
            $defaultMutators,
            $whitelistedMutatorNames,
            $eventDispatcherMock,
            $this->getParser()
        );
    }

    private function getParser(): Parser
    {
        $lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos',
            ],
        ]);

        return (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
    }
}
