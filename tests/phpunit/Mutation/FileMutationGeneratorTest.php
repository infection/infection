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

namespace Infection\Tests\Mutation;

use function current;
use function func_get_args;
use Generator;
use Infection\Console\InfectionContainer;
use Infection\Mutation;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\FileParser;
use Infection\Mutation\NodeTraverserFactory;
use Infection\Mutation\PrioritizedVisitorsNodeTraverser;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\IgnoreMutator;
use Infection\Mutator\Util\MutatorConfig;
use Infection\TestFramework\Coverage\LineCodeCoverage;
use Infection\Tests\Fixtures\PhpParser\FakeNode;
use Infection\Tests\Fixtures\PhpParser\FakeVisitor;
use Infection\Visitor\MutationsCollectorVisitor;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\sprintf;
use Symfony\Component\Finder\SplFileInfo;

final class FileMutationGeneratorTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../Fixtures/Files';

    /**
     * @var FileParser|MockObject
     */
    private $fileParserMock;

    /**
     * @var NodeTraverserFactory|MockObject
     */
    private $traverserFactoryMock;

    /**
     * @var FileMutationGenerator|MockObject
     */
    private $mutationGenerator;

    protected function setUp(): void
    {
        $this->fileParserMock = $this->createMock(FileParser::class);
        $this->traverserFactoryMock = $this->createMock(NodeTraverserFactory::class);

        $this->mutationGenerator = new FileMutationGenerator(
            $this->fileParserMock,
            $this->traverserFactoryMock
        );
    }

    public function test_it_generates_mutations_for_a_given_file(): void
    {
        $codeCoverageMock = $this->createMock(LineCodeCoverage::class);

        $container = InfectionContainer::create();

        /** @var FileMutationGenerator $mutationGenerator */
        $mutationGenerator = $container[FileMutationGenerator::class];

        $mutatorConfig = new MutatorConfig([]);

        $mutations = $mutationGenerator->generate(
            new SplFileInfo(self::FIXTURES_DIR . '/Mutation/OneFile/OneFile.php', '', ''),
            false,
            $codeCoverageMock,
            [new IgnoreMutator($mutatorConfig, new Plus($mutatorConfig))],
            []
        );

        foreach ($mutations as $mutation) {
            $this->assertInstanceOf(Mutation::class, $mutation);
        }

        $this->assertCount(1, $mutations);
        $this->assertArrayHasKey(0, $mutations);

        /** @var Mutation $mutation */
        $mutation = current($mutations);

        $this->assertSame(Plus::class, $mutation->getMutatorClass());
    }

    /**
     * @dataProvider parsedFilesProvider
     */
    public function test_it_attempts_to_generate_mutations_for_the_file_if_covered_or_not_only_covered_code(
        SplFileInfo $fileInfo,
        bool $onlyCovered,
        LineCodeCoverage $codeCoverage,
        string $expectedFilePath
    ): void {
        $extraVisitors = [2 => new FakeVisitor()];

        $this->fileParserMock
            ->expects($this->once())
            ->method('parse')
            ->with($expectedFilePath)
            ->willReturn($initialStatements = [
                new FakeNode(),
                new FakeNode(),
            ])
        ;

        $traverserMock = $this->createMock(PrioritizedVisitorsNodeTraverser::class);
        $traverserMock
            ->expects($this->once())
            ->method('traverse')
            ->willReturn($initialStatements)
        ;

        $this->traverserFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $passedExtraNodeVisitors) use ($extraVisitors) {
                $this->assertSame([$passedExtraNodeVisitors], func_get_args());

                $this->assertArrayHasKey(10, $passedExtraNodeVisitors);
                $this->assertInstanceOf(MutationsCollectorVisitor::class, $passedExtraNodeVisitors[10]);

                unset($passedExtraNodeVisitors[10]);

                $this->assertSame($extraVisitors, $passedExtraNodeVisitors);

                return true;
            }))
            ->willReturn($traverserMock)
        ;

        $mutationGenerator = new FileMutationGenerator(
            $this->fileParserMock,
            $this->traverserFactoryMock
        );

        $mutatorConfig = new MutatorConfig([]);

        $mutations = $mutationGenerator->generate(
            $fileInfo,
            $onlyCovered,
            $codeCoverage,
            [new IgnoreMutator($mutatorConfig, new Plus($mutatorConfig))],
            $extraVisitors
        );

        $this->assertSame([], $mutations);
    }

    /**
     * @dataProvider skippedFilesProvider
     */
    public function test_it_skips_the_mutation_generation_if_checks_only_covered_code_and_the_file_has_no_tests(
        SplFileInfo $fileInfo,
        string $expectedFilePath
    ): void {
        $this->fileParserMock
            ->expects($this->never())
            ->method('parse')
        ;

        $this->traverserFactoryMock
            ->expects($this->never())
            ->method('create')
        ;

        $mutationGenerator = new FileMutationGenerator(
            $this->fileParserMock,
            $this->traverserFactoryMock
        );

        $mutatorConfig = new MutatorConfig([]);

        $mutations = $mutationGenerator->generate(
            $fileInfo,
            true,
            $this->createCodeCoverageMock(
                $expectedFilePath,
                false
            ),
            [new IgnoreMutator($mutatorConfig, new Plus($mutatorConfig))],
            []
        );

        $this->assertSame([], $mutations);
    }

    public function test_it_cannot_generate_mutations_if_a_visitor_is_already_registered_instead_of_the_mutation_collector_visitor(): void
    {
        $fileInfo = new SplFileInfo('/path/to/file', 'relativePath', 'relativePathName');

        $extraVisitors = [10 => new FakeVisitor()];

        $fileParserMock = $this->createMock(FileParser::class);
        $fileParserMock
            ->expects($this->never())
            ->method('parse')
        ;

        $traverserFactoryMock = $this->createMock(NodeTraverserFactory::class);
        $traverserFactoryMock
            ->expects($this->never())
            ->method('create')
        ;

        $mutationGenerator = new FileMutationGenerator(
            $fileParserMock,
            $traverserFactoryMock
        );

        $mutatorConfig = new MutatorConfig([]);

        try {
            $mutationGenerator->generate(
                $fileInfo,
                false,
                $this->createMock(LineCodeCoverage::class),
                [new IgnoreMutator($mutatorConfig, new Plus($mutatorConfig))],
                $extraVisitors
            );

            $this->fail('Expected an exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                sprintf(
                    'Did not expect to find a visitor for the priority "10". Found "%s". '
                    . 'Please free that priority as it is reserved for "%s".',
                    FakeVisitor::class,
                    MutationsCollectorVisitor::class
                ),
                $exception->getMessage()
            );
        }
    }

    public function parsedFilesProvider(): Generator
    {
        foreach ($this->provideBoolean() as $hasTests) {
            $title = sprintf(
                'path - only covered: false - has tests: %s',
                $hasTests ? 'true' : 'false'
            );

            yield $title => [
                new SplFileInfo('/path/to/file', 'relativePath', 'relativePathName'),
                false,
                $this->createCodeCoverageMock(
                    '/path/to/file',
                    true
                ),
                '/path/to/file',
            ];
        }

        foreach ($this->provideBoolean() as $hasTests) {
            $title = sprintf(
                'real path - only covered: false - has tests: %s',
                $hasTests ? 'true' : 'false'
            );

            yield $title => [
                new SplFileInfo(__FILE__, 'relativePath', 'relativePathName'),
                false,
                $this->createCodeCoverageMock(
                    __FILE__,
                    true
                ),
                __FILE__,
            ];
        }

        yield 'path - only covered: true - has tests: %s' => [
            new SplFileInfo('/path/to/file', 'relativePath', 'relativePathName'),
            true,
            $this->createCodeCoverageMock(
                '/path/to/file',
                true
            ),
            '/path/to/file',
        ];

        yield 'real path - only covered: true - has tests: %s' => [
            new SplFileInfo(__FILE__, 'relativePath', 'relativePathName'),
            true,
            $this->createCodeCoverageMock(
                __FILE__,
                true
            ),
            __FILE__,
        ];
    }

    public function skippedFilesProvider(): Generator
    {
        yield 'path - only covered: true - has tests: %s' => [
            new SplFileInfo('/path/to/file', 'relativePath', 'relativePathName'),
            '/path/to/file',
        ];

        yield 'real path - only covered: true - has tests: %s' => [
            new SplFileInfo(__FILE__, 'relativePath', 'relativePathName'),
            __FILE__,
        ];
    }

    public function provideBoolean(): Generator
    {
        yield from [true, false];
    }

    /**
     * @return LineCodeCoverage|MockObject
     */
    private function createCodeCoverageMock(string $expectedPath, bool $tests)
    {
        $codeCoverageMock = $this->createMock(LineCodeCoverage::class);
        $codeCoverageMock
            ->method('hasTests')
            ->with($expectedPath)
            ->willReturn($tests)
        ;

        return $codeCoverageMock;
    }
}
