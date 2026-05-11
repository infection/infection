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

namespace Infection\Tests\PhpParser\Traverser;

use Infection\Event\Events\Ast\AstEnrichment\AstEnrichmentWasFinished;
use Infection\Event\Events\Ast\AstEnrichment\AstEnrichmentWasStarted;
use Infection\PhpParser\Traverser\EnrichmentNodeTraverser;
use Infection\Testing\FileSystem\MockSplFileInfo;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\Fixtures\PhpParser\FakeNode;
use Infection\Tests\Fixtures\PhpParser\FakeVisitor;
use PhpParser\NodeTraverserInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnrichmentNodeTraverser::class)]
final class EnrichmentNodeTraverserTest extends TestCase
{
    #[DataProvider('sourceFileProvider')]
    public function test_it_dispatches_enrichment_events_around_the_decorated_traverser(
        MockSplFileInfo $sourceFile,
        string $expectedSourceFilePath,
    ): void {
        $eventDispatcher = new EventDispatcherCollector();
        $nodes = [];
        $enrichedNodes = [new FakeNode()];

        $decoratedTraverserMock = $this->createMock(NodeTraverserInterface::class);
        $decoratedTraverserMock
            ->expects($this->once())
            ->method('traverse')
            ->with($nodes)
            ->willReturn($enrichedNodes);

        $traverser = new EnrichmentNodeTraverser($sourceFile, $decoratedTraverserMock, $eventDispatcher);

        $this->assertSame($enrichedNodes, $traverser->traverse($nodes));
        $this->assertEquals(
            [
                new AstEnrichmentWasStarted($expectedSourceFilePath),
                new AstEnrichmentWasFinished($expectedSourceFilePath),
            ],
            $eventDispatcher->getEvents(),
        );
    }

    public function test_it_delegates_visitor_management_to_the_decorated_traverser(): void
    {
        $sourceFile = new MockSplFileInfo('/path/to/source.php');
        $eventDispatcher = new EventDispatcherCollector();
        $visitor = new FakeVisitor();

        $decoratedTraverserMock = $this->createMock(NodeTraverserInterface::class);
        $decoratedTraverserMock
            ->expects($this->once())
            ->method('addVisitor')
            ->with($visitor);
        $decoratedTraverserMock
            ->expects($this->once())
            ->method('removeVisitor')
            ->with($visitor);

        $traverser = new EnrichmentNodeTraverser(
            $sourceFile,
            $decoratedTraverserMock,
            $eventDispatcher,
        );

        $traverser->addVisitor($visitor);
        $traverser->removeVisitor($visitor);

        $this->assertSame([], $eventDispatcher->getEvents());
    }

    public static function sourceFileProvider(): iterable
    {
        yield 'real path available' => [
            new MockSplFileInfo('/path/to/source.php', '/real/path/to/source.php'),
            '/real/path/to/source.php',
        ];

        yield 'real path unavailable' => [
            new MockSplFileInfo('/path/to/source.php'),
            '/path/to/source.php',
        ];
    }
}
