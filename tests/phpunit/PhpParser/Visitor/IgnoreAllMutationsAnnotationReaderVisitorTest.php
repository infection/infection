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

namespace Infection\Tests\PhpParser\Visitor;

use Infection\PhpParser\Visitor\IgnoreAllMutationsAnnotationReaderVisitor;
use Infection\PhpParser\Visitor\IgnoreNode\ChangingIgnorer;
use PhpParser\Comment;
use PhpParser\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use SplObjectStorage;

#[Group('integration')]
#[CoversClass(IgnoreAllMutationsAnnotationReaderVisitor::class)]
final class IgnoreAllMutationsAnnotationReaderVisitorTest extends BaseVisitorTestCase
{
    public function test_it_does_not_toggle_ignorer_for_nodes_without_comments(): void
    {
        $changingIgnorer = $this->createMock(ChangingIgnorer::class);
        $changingIgnorer
            ->expects($this->never())
            ->method($this->anything())
        ;

        $ignoredNodes = new SplObjectStorage();

        $visitor = new IgnoreAllMutationsAnnotationReaderVisitor($changingIgnorer, $ignoredNodes);

        $nodeWithoutComments = $this->createMock(Node::class);
        $nodeWithoutComments
            ->expects($this->once())
            ->method('getComments')
            ->willReturn([])
        ;

        $visitor->enterNode($nodeWithoutComments);

        $this->assertCount(0, $ignoredNodes);
    }

    public function test_it_does_not_toggle_ignorer_for_nodes_with_comments_without_expected_annotation(): void
    {
        $changingIgnorer = $this->createMock(ChangingIgnorer::class);
        $changingIgnorer
            ->expects($this->never())
            ->method($this->anything())
        ;

        $ignoredNodes = new SplObjectStorage();

        $visitor = new IgnoreAllMutationsAnnotationReaderVisitor($changingIgnorer, $ignoredNodes);

        $comment = $this->createMock(Comment::class);
        $comment
            ->expects($this->once())
            ->method('getText')
            ->willReturn('This is a test')
        ;

        $nodeWithComments = $this->createMock(Node::class);

        $nodeWithComments
            ->expects($this->once())
            ->method('getComments')
            ->willReturn([$comment])
        ;

        $visitor->enterNode($nodeWithComments);

        $this->assertCount(0, $ignoredNodes);
    }

    public function test_it_toggles_ignorer_for_nodes_commented_with_expected_annotation(): void
    {
        $changingIgnorer = $this->createMock(ChangingIgnorer::class);
        $changingIgnorer
            ->expects($this->once())
            ->method('startIgnoring')
        ;

        $ignoredNodes = new SplObjectStorage();

        $visitor = new IgnoreAllMutationsAnnotationReaderVisitor($changingIgnorer, $ignoredNodes);

        $comment = $this->createMock(Comment::class);
        $comment
            ->expects($this->once())
            ->method('getText')
            ->willReturn('@infection-ignore-all')
        ;

        $nodeWithComments = $this->createMock(Node::class);

        $nodeWithComments
            ->expects($this->once())
            ->method('getComments')
            ->willReturn([$comment])
        ;

        $visitor->enterNode($nodeWithComments);

        $this->assertCount(1, $ignoredNodes);
    }

    public function test_it_stops_ignorer_when_leaving_node_it_started_ignoring_with(): void
    {
        $changingIgnorer = $this->createMock(ChangingIgnorer::class);
        $changingIgnorer
            ->expects($this->once())
            ->method('stopIgnoring')
        ;

        $node = $this->createMock(Node::class);

        $ignoredNodes = new SplObjectStorage();
        $ignoredNodes->attach($node);

        $visitor = new IgnoreAllMutationsAnnotationReaderVisitor($changingIgnorer, $ignoredNodes);

        $visitor->leaveNode($node);

        $this->assertCount(0, $ignoredNodes);
    }
}
