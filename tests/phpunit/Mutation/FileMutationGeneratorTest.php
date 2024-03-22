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
use Infection\Differ\FilesDiffChangedLines;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\PhpParser\FileParser;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\Visitor\MutationCollectorVisitor;
use Infection\TestFramework\Coverage\LineRangeCalculator;
use Infection\TestFramework\Coverage\Trace;
use Infection\Tests\Fixtures\PhpParser\FakeIgnorer;
use Infection\Tests\Fixtures\PhpParser\FakeNode;
use Infection\Tests\Mutator\MutatorName;
use Infection\Tests\SingletonContainer;
use function iterator_to_array;
use PhpParser\NodeTraverserInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Finder\SplFileInfo;

#[CoversClass(Infection\Mutation\FileMutationGenerator::class)]
final class FileMutationGeneratorTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../Fixtures/Files';

    /**
     * @var FileParser&MockObject
     */
    private $fileParserMock;

    /**
     * @var NodeTraverserFactory&MockObject
     */
    private $traverserFactoryMock;

    private FileMutationGenerator $mutationGenerator;

    /**
     * @var FilesDiffChangedLines&MockObject
     */
    private $filesDiffChangedLines;

    protected function setUp(): void
    {
        $this->fileParserMock = $this->createMock(FileParser::class);
        $this->traverserFactoryMock = $this->createMock(NodeTraverserFactory::class);
        $this->filesDiffChangedLines = $this->createMock(FilesDiffChangedLines::class);

        $this->mutationGenerator = new FileMutationGenerator(
            $this->fileParserMock,
            $this->traverserFactoryMock,
            new LineRangeCalculator(),
            $this->filesDiffChangedLines,
            false,
            'master',
        );
    }

    public function test_it_generates_mutations_for_a_given_file(): void
    {
        $traceMock = $this->createTraceMock(
            self::FIXTURES_DIR . '/Mutation/OneFile/OneFile.php',
            '',
            '',
        );
        $traceMock
            ->expects($this->never())
            ->method('hasTests')
        ;
        $traceMock
            ->expects($this->once())
            ->method('getAllTestsForMutation')
            ->willReturn([])
        ;
        $traceMock
            ->expects($this->once())
            ->method('getRealPath')
            ->willReturn(self::FIXTURES_DIR . '/Mutation/OneFile/OneFile.php')
        ;

        $mutationGenerator = SingletonContainer::getContainer()->getFileMutationGenerator();

        $mutations = $mutationGenerator->generate(
            $traceMock,
            false,
            [new IgnoreMutator(new IgnoreConfig([]), new Plus())],
            [],
        );

        $mutations = iterator_to_array($mutations, false);

        foreach ($mutations as $mutation) {
            $this->assertInstanceOf(Mutation::class, $mutation);
        }

        $this->assertCount(1, $mutations);
        $this->assertArrayHasKey(0, $mutations);

        /** @var Mutation $mutation */
        $mutation = current($mutations);

        $this->assertSame(
            MutatorName::getName(Plus::class),
            $mutation->getMutatorName(),
        );
    }

    #[DataProvider('parsedFilesProvider')]
    public function test_it_attempts_to_generate_mutations_for_the_file_if_covered_or_not_only_covered_code(
        string $file,
        string $relativePath,
        string $relativePathname,
        ?bool $hasTests,
        bool $onlyCovered,
        string $expectedFilePath,
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
            ->with($this->isInstanceOf(MutationCollectorVisitor::class), $nodeIgnorers)
            ->willReturn($traverserMock)
        ;

        $trace = $this->createTraceMock($file, $relativePath, $relativePathname, $hasTests);

        $mutations = $this->mutationGenerator->generate(
            $trace,
            $onlyCovered,
            [new IgnoreMutator(new IgnoreConfig([]), new Plus())],
            $nodeIgnorers,
        );

        $mutations = iterator_to_array($mutations, false);

        $this->assertSame([], $mutations);
    }

    #[DataProvider('skippedFilesProvider')]
    public function test_it_skips_the_mutation_generation_if_checks_only_covered_code_and_the_file_has_no_tests(
        string $file,
        string $relativePath,
        string $relativePathname,
        ?bool $hasTests = null,
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
            new LineRangeCalculator(),
            $this->filesDiffChangedLines,
            false,
            'master',
        );

        $trace = $this->createTraceMock($file, $relativePath, $relativePathname, $hasTests);

        $mutations = $mutationGenerator->generate(
            $trace,
            true,
            [new IgnoreMutator(new IgnoreConfig([]), new Plus())],
            [],
        );

        $mutations = iterator_to_array($mutations, false);

        $this->assertSame([], $mutations);
    }

    public static function parsedFilesProvider(): iterable
    {
        foreach (self::provideBoolean() as $hasTests) {
            $title = sprintf(
                'path - only covered: false - has tests: %s',
                $hasTests ? 'true' : 'false',
            );

            yield $title => [
                '/path/to/file',
                'relativePath',
                'relativePathName',
                true,
                false,
                '/path/to/file',
            ];
        }

        foreach (self::provideBoolean() as $hasTests) {
            $title = sprintf(
                'real path - only covered: false - has tests: %s',
                $hasTests ? 'true' : 'false',
            );

            yield $title => [
                __FILE__,
                'relativePath',
                'relativePathName',
                true,
                false,
                __FILE__,
            ];
        }

        yield 'path - only covered: true - has tests: %s' => [
            '/path/to/file',
            'relativePath',
            'relativePathName',
            true,
            true,
            '/path/to/file',
        ];

        yield 'real path - only covered: true - has tests: %s' => [
            __FILE__,
            'relativePath',
            'relativePathName',
            true,
            true,
            __FILE__,
        ];
    }

    public static function skippedFilesProvider(): iterable
    {
        yield 'path - only covered: true - has tests: %s' => [
            '/path/to/file',
            'relativePath',
            'relativePathName',
            false,
        ];

        yield 'real path - only covered: true - has tests: %s' => [
            __FILE__,
            'relativePath',
            'relativePathName',
            false,
        ];
    }

    public static function provideBoolean(): iterable
    {
        yield from [true, false];
    }

    /**
     * @return Trace|MockObject
     */
    private function createTraceMock(
        string $file,
        string $relativePath,
        string $relativePathname,
        ?bool $hasTests = null,
    ): Trace {
        $proxyTraceMock = $this->createMock(Trace::class);
        $proxyTraceMock
            ->method('getSourceFileInfo')
            ->willReturn(new SplFileInfo($file, $relativePath, $relativePathname))
        ;

        if ($hasTests !== null) {
            $proxyTraceMock
                ->method('hasTests')
                ->willReturn($hasTests)
            ;
        }

        return $proxyTraceMock;
    }
}
