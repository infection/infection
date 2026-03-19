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

namespace Infection\Tests\PhpParser;

use function array_map;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\Visitor\IgnoreAllMutationsAnnotationReaderVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\NextConnectingVisitor;
use Infection\PhpParser\Visitor\NonMutableNodesIgnorerVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Tests\Fixtures\PhpParser\FakeVisitor;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

#[CoversClass(NodeTraverserFactory::class)]
final class NodeTraverserFactoryTest extends TestCase
{
    private static ?ReflectionProperty $visitorsReflection = null;

    private NodeTraverserFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new NodeTraverserFactory();
    }

    public function test_it_can_create_a_traverser_for_enriching_the_ast(): void
    {
        $traverser = $this->factory->createEnrichmentTraverser(
            new MockSplFileInfo(realPath: '/path/to/virtual-test-file.php'),
        );

        $this->assertTraverserVisitorsAre(
            $traverser,
            [
                NextConnectingVisitor::class,
                IgnoreAllMutationsAnnotationReaderVisitor::class,
                NonMutableNodesIgnorerVisitor::class,
                NameResolver::class,
                ParentConnectingVisitor::class,
                ReflectionVisitor::class,
                LabelNodesAsEligibleVisitor::class,
            ],
        );
    }

    public function test_it_can_create_a_traverser_for_generating_mutations(): void
    {
        $traverser = $this->factory->createMutationTraverser(
            new FakeVisitor(),
        );

        $this->assertTraverserVisitorsAre(
            $traverser,
            [
                CloningVisitor::class,
                FakeVisitor::class,
            ],
        );
    }

    /**
     * @param list<class-string<NodeVisitor>> $expected
     */
    private function assertTraverserVisitorsAre(
        NodeTraverserInterface $traverser,
        array $expected,
    ): void {
        // Sanity check. This is not a hard constraint, but if that changes in the future, then we need
        // to adapt the code here to retrieve the visitors a different way.
        $this->assertInstanceOf(NodeTraverser::class, $traverser);

        $actual = self::getVisitorClassNames($traverser);

        $this->assertSame($expected, $actual);
    }

    /**
     * @return list<class-string<NodeVisitor>>
     */
    private static function getVisitorClassNames(NodeTraverser $traverser): array
    {
        /** @var list<NodeVisitor> $visitors */
        $visitors = self::getVisitorReflection()->getValue($traverser);

        return array_map(
            get_class(...),
            $visitors,
        );
    }

    private static function getVisitorReflection(): ReflectionProperty
    {
        return self::$visitorsReflection ??= (new ReflectionClass(NodeTraverser::class))->getProperty('visitors');
    }
}
