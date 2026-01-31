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

namespace Infection\Tests\Mutation\FileMutationGenerator;

use Infection\FileSystem\FileStore;
use Infection\FileSystem\FileSystem;
use Infection\Mutation\FileMutationGenerator;
use Infection\PhpParser\FileParser;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\Source\Matcher\SourceLineMatcher;
use Infection\TestFramework\Tracing\Throwable\NoTraceFound;
use Infection\TestFramework\Tracing\Trace\LineRangeCalculator;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\Tracer;
use Infection\Tests\Fixtures\Mutator\FakeMutator;
use Infection\Tests\Fixtures\PhpParser\FakeNode;
use Infection\Tests\PhpParser\FakeToken;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use function iterator_to_array;
use PhpParser\NodeTraverserInterface;
use PhpParser\Parser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(FileMutationGenerator::class)]
final class FileMutationGeneratorTest extends TestCase
{
    private MockObject&Parser $phpParserMock;

    private MockObject&NodeTraverserFactory $traverserFactoryMock;

    private MockObject&Tracer $tracerMock;

    private FileMutationGenerator $mutationGenerator;

    protected function setUp(): void
    {
        $this->phpParserMock = $this->createMock(Parser::class);
        $this->traverserFactoryMock = $this->createMock(NodeTraverserFactory::class);
        $this->tracerMock = $this->createMock(Tracer::class);

        $fileSystemStub = $this->createStub(FileSystem::class);
        $fileSystemStub
            ->method('readFile')
            ->willReturnCallback(
                static fn (string $path): string => sprintf(
                    'contents(%s)',
                    $path,
                ),
            );

        $this->mutationGenerator = new FileMutationGenerator(
            new FileParser(
                $this->phpParserMock,
                new FileStore($fileSystemStub),
            ),
            $this->traverserFactoryMock,
            new LineRangeCalculator(),
            $this->createMock(SourceLineMatcher::class),
            $this->tracerMock,
            new FileStore($fileSystemStub),
        );
    }

    public function test_it_parses_the_source_file_and_yields_the_generated_mutations(): void
    {
        $fileInfoMock = new MockSplFileInfo(realPath: '/path/to/file');

        $mutators = [
            new FakeMutator(),
            new FakeMutator(),
        ];

        $initialStatements = [
            new FakeNode(),
            new FakeNode(),
        ];

        $originalFileTokens = [
            FakeToken::create(),
            FakeToken::create(),
        ];

        $this->phpParserMock
            ->expects($this->once())
            ->method('parse')
            ->with('contents(/path/to/file)')
            ->willReturn($initialStatements);
        $this->phpParserMock
            ->expects($this->once())
            ->method('getTokens')
            ->willReturn($originalFileTokens);

        $preTraverserCalled = false;

        // Pre-traverser should be created and called first
        $preTraverserMock = $this->createMock(NodeTraverserInterface::class);
        $preTraverserMock
            ->expects($this->once())
            ->method('traverse')
            ->with($initialStatements)
            ->willReturnCallback(
                static function () use (&$preTraverserCalled) {
                    $preTraverserCalled = true;

                    // The return value is not used. In practice, we directly mutate the
                    // original value, but this cannot be mimicked with mocks.
                    return [];
                },
            );

        // Main traverser should be created and called after
        $traverserMock = $this->createMock(NodeTraverserInterface::class);
        $traverserMock
            ->expects($this->once())
            ->method('traverse')
            ->with($initialStatements)
            ->willReturnCallback(
                static function () use (&$preTraverserCalled) {
                    self::assertTrue($preTraverserCalled);

                    // The return value is not used. In practice, we directly mutate the
                    // original value, but this cannot be mimicked with mocks.
                    return [];
                },
            );

        // Set up expectations in order
        $this->traverserFactoryMock
            ->expects($this->exactly(2))
            ->method($this->anything())
            ->willReturnOnConsecutiveCalls($preTraverserMock, $traverserMock);

        $traceMock = $this->createMock(Trace::class);
        $traceMock
            ->expects($this->never())
            ->method('hasTests');

        $this->tracerMock
            ->method('trace')
            ->with($fileInfoMock)
            ->willReturn($traceMock);

        $mutations = $this->mutationGenerator->generate(
            $fileInfoMock,
            false,
            $mutators,
        );

        // We cannot really check more than that here as controlling the mutations yielded
        // would require mocking the visitor, which cannot be done here.
        // Instead, we limit ourselves to check that the traverser is called.
        $this->assertCount(
            0,
            iterator_to_array($mutations, false),
        );
    }

    /**
     * This test is fairly limited as, due to relying on an instantiated visitor, we cannot
     * control what mutations are yielded. So instead, this test only checks that the source
     * file was parsed and traversed, which we equate to having mutations generated.
     */
    #[DataProvider('scenarioProvider')]
    public function test_it_traverses_the_source_statements(
        Scenario $scenario,
    ): void {
        $fileInfoMock = new MockSplFileInfo(realPath: '/path/to/file');

        $mutators = [
            new FakeMutator(),
            new FakeMutator(),
        ];

        $initialStatements = [
            new FakeNode(),
            new FakeNode(),
        ];
        $originalFileTokens = [
            FakeToken::create(),
            FakeToken::create(),
        ];

        $traverserStub = $this->createMock(NodeTraverserInterface::class);
        $traverserStub
            ->method('traverse')
            ->willReturn([]);

        if ($scenario->expected) {
            $this->phpParserMock
                ->expects($this->once())
                ->method('parse')
                ->willReturn($initialStatements);
            $this->phpParserMock
                ->expects($this->once())
                ->method('getTokens')
                ->willReturn($originalFileTokens);

            $this->traverserFactoryMock
                ->method('createPreTraverser')
                ->willReturn($traverserStub);
        } else {
            $this->phpParserMock
                ->expects($this->never())
                ->method('parse');

            $this->traverserFactoryMock
                ->expects($this->never())
                ->method('createPreTraverser')
                ->willReturn($traverserStub);
        }

        $traceMock = $this->createMock(Trace::class);
        $traceMock
            ->method('hasTests')
            ->willReturn($scenario->traceHasTests);

        if ($scenario->hasTrace) {
            $this->tracerMock
                ->method('trace')
                ->willReturn($traceMock);
        } else {
            $this->tracerMock
                ->method('trace')
                ->willThrowException(new NoTraceFound());
        }

        $mutations = $this->mutationGenerator->generate(
            $fileInfoMock,
            $scenario->onlyCovered,
            $mutators,
        );

        // See the test description: we do not check the result itself only the mocks
        // expectations.
        $mutations = iterator_to_array($mutations, false);
        $this->assertSame([], $mutations);
    }

    public static function scenarioProvider(): iterable
    {
        $nominalScenario = new Scenario(
            onlyCovered: true,
            hasTrace: true,
            traceHasTests: true,
            expected: true,
        );

        yield 'nominal' => [$nominalScenario];

        yield 'onlyCovered=true: skip generation if no tests' => [
            $nominalScenario
                ->withTraceHasTests(false)
                ->withExpected(false),
        ];

        yield 'onlyCovered=true: skip generation if no trace' => [
            $nominalScenario
                ->withHasTrace(false)
                ->withExpected(false),
        ];

        yield 'onlyCovered=false: do not skip generation if no tests' => [
            $nominalScenario
                ->withOnlyCovered(false)
                ->withTraceHasTests(false),
        ];

        yield 'onlyCovered=false: do not skip generation if no trace' => [
            $nominalScenario
                ->withOnlyCovered(false)
                ->withHasTrace(false),
        ];

        yield 'onlyCovered=false: do not skip generation if no trace and no tests' => [
            $nominalScenario
                ->withOnlyCovered(false)
                ->withHasTrace(false)
                ->withTraceHasTests(false),
        ];
    }
}
