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

namespace Infection\Tests\TestFramework\Tracing\Trace;

use Infection\Testing\SingletonContainer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RangeSpyVisitor::class)]
final class RangeSpyVisitorTest extends TestCase
{
    public function test_it_records_the_range_of_a_variable_of_interest(): void
    {
        $range = $this->traverseAndGetRange(
            <<<'PHP'
                <?php

                function foo(): void
                {
                    (static function() {
                        $a = $findMe;
                    })();
                }
                PHP,
        );

        $this->assertSame([6], $range);
    }

    public function test_it_records_the_single_line_range_of_a_function_signature_of_interest(): void
    {
        $range = $this->traverseAndGetRange(
            <<<'PHP'
                <?php

                class Test {
                    public function findMe() // line 4
                    {
                        // ...
                    }
                }
                PHP,
        );

        $this->assertSame([4], $range);
    }

    public function test_it_leaves_the_range_untouched_when_nothing_of_interest_is_visited(): void
    {
        $range = $this->traverseAndGetRange(
            <<<'PHP'
                <?php

                $x = 'Hello World!';
                PHP,
        );

        $this->assertSame([], $range);
    }

    /**
     * @return int[]
     */
    private function traverseAndGetRange(string $code): array
    {
        $nodes = SingletonContainer::getContainer()->getParser()->parse($code);

        $spy = new RangeSpyVisitor();

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($spy);
        $traverser->traverse($nodes ?? []);

        return $spy->range;
    }
}
