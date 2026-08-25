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

namespace Infection\Tests\Source\Matcher\SourceSymbolMatcher;

use Infection\Configuration\SourceSymbolSelector;
use Infection\PhpParser\Visitor\NameResolverFactory;
use Infection\Source\Matcher\SourceSymbolMatcher;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SourceSymbolMatcher::class)]
final class SourceSymbolMatcherTest extends VisitorTestCase
{
    public function test_it_matches_a_multiline_node_in_the_selected_method(): void
    {
        $nodes = $this->parse(<<<'PHP'
            <?php
            namespace App;
            final class Mailer {
                public function send(): int {
                    return 1
                        + 2;
                }
            }
            PHP);

        (new NodeTraverser(NameResolverFactory::create(), new ParentConnectingVisitor()))->traverse($nodes);

        $binaryOperation = (new NodeFinder())->findFirstInstanceOf($nodes, Node\Expr\BinaryOp::class);
        $this->assertInstanceOf(Node\Expr\BinaryOp::class, $binaryOperation);
        $matcher = new SourceSymbolMatcher();

        $this->assertTrue($matcher->matches($binaryOperation, new SourceSymbolSelector('App\Mailer', 'send', 6)));
        $this->assertFalse($matcher->matches($binaryOperation, new SourceSymbolSelector('App\Mailer', 'other', 6)));
        $this->assertFalse($matcher->matches($binaryOperation, new SourceSymbolSelector('App\Other', 'send', 6)));
    }
}
