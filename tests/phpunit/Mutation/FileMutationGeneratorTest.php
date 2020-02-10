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
use Generator;
use Infection\Container;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\PhpParser\FileParser;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\Visitor\MutationsCollectorVisitor;
use Infection\TestFramework\Coverage\LineCodeCoverage;
use Infection\TestFramework\Coverage\LineRangeCalculator;
use Infection\Tests\Fixtures\PhpParser\FakeIgnorer;
use Infection\Tests\Fixtures\PhpParser\FakeNode;
use Infection\Tests\Mutator\MutatorName;
use PhpParser\NodeTraverserInterface;
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
            $this->traverserFactoryMock,
            new LineRangeCalculator()
        );
    }

    public function test_it_generates_mutations_for_a_given_file(): void
    {
        $codeCoverageMock = $this->createMock(LineCodeCoverage::class);

        $mutationGenerator = Container::create()->getFileMutationGenerator();

        $mutations = $mutationGenerator->generate(
            new SplFileInfo(self::FIXTURES_DIR . '/Mutation/OneFile/OneFile.php', '', ''),
            false,
            $codeCoverageMock,
            [new IgnoreMutator(new IgnoreConfig([]), new Plus())],
            []
        );

        foreach ($mutations as $mutation) {
            $this->assertInstanceOf(Mutation::class, $mutation);
        }

        $this->assertCount(1, $mutations);
        $this->assertArrayHasKey(0, $mutations);

        /** @var Mutation $mutation */
        $mutation = current($mutations);

        $this->assertSame(
            MutatorName::getName(Plus::class),
            $mutation->getMutatorName()
        );
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
        $nodeIgnorers = [new FakeIgnorer()];

        $this->fileParserMock
            ->expects($this->once())
            ->method('parse')
            ->with($expectedFilePath)
            ->willReturn($initialStatements = [
                new FakeNode(),
                new FakeNode(),
            ])
        ;

        $traverserMock = $this->createMock(NodeTraverserInterface::class);
        $traverserMock
            ->expects($this->once())
            ->method('traverse')
            ->willReturn($initialStatements)
        ;

        $this->traverserFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(MutationsCollectorVisitor::class), $nodeIgnorers)
            ->willReturn($traverserMock)
        ;

        $mutations = $this->mutationGenerator->generate(
            $fileInfo,
            $onlyCovered,
            $codeCoverage,
            [new IgnoreMutator(new IgnoreConfig([]), new Plus())],
            $nodeIgnorers
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
            $this->traverserFactoryMock,
            new LineRangeCalculator()
        );

        $mutations = $mutationGenerator->generate(
            $fileInfo,
            true,
            $this->createCodeCoverageMock(
                $expectedFilePath,
                false
            ),
            [new IgnoreMutator(new IgnoreConfig([]), new Plus())],
            []
        );

        $this->assertSame([], $mutations);
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
