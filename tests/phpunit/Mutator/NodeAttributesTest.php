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

namespace Infection\Tests\Mutator;

use Infection\Mutator\NodeAttributes;
use PhpParser\Node\Scalar\Int_;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NodeAttributes::class)]
final class NodeAttributesTest extends TestCase
{
    public function test_it_returns_all_attributes_except_original_node(): void
    {
        $node = new Int_(42);
        $node->setAttribute('startLine', 1);
        $node->setAttribute('endLine', 1);
        $node->setAttribute('startTokenPos', 5);
        $node->setAttribute('endTokenPos', 10);
        $node->setAttribute('origNode', new Int_(24));
        $node->setAttribute('customAttribute', 'value');

        $result = NodeAttributes::getAllExceptOriginalNode($node);

        $this->assertArrayNotHasKey('origNode', $result);
        $this->assertSame(1, $result['startLine']);
        $this->assertSame(1, $result['endLine']);
        $this->assertSame(5, $result['startTokenPos']);
        $this->assertSame(10, $result['endTokenPos']);
        $this->assertSame('value', $result['customAttribute']);
    }

    public function test_it_returns_empty_array_when_node_has_no_attributes(): void
    {
        $node = new Int_(42);

        $result = NodeAttributes::getAllExceptOriginalNode($node);

        $this->assertSame([], $result);
    }

    public function test_it_returns_all_attributes_when_original_node_is_not_present(): void
    {
        $node = new Int_(42);
        $node->setAttribute('startLine', 1);
        $node->setAttribute('endLine', 1);

        $result = NodeAttributes::getAllExceptOriginalNode($node);

        $this->assertArrayHasKey('startLine', $result);
        $this->assertArrayHasKey('endLine', $result);
        $this->assertArrayNotHasKey('origNode', $result);
        $this->assertCount(2, $result);
    }

    public function test_it_only_removes_original_node_attribute(): void
    {
        $node = new Int_(42);
        $node->setAttribute('origNode', new Int_(24));
        $node->setAttribute('otherAttribute', 'should-remain');

        $result = NodeAttributes::getAllExceptOriginalNode($node);

        $this->assertArrayNotHasKey('origNode', $result);
        $this->assertArrayHasKey('otherAttribute', $result);
        $this->assertSame('should-remain', $result['otherAttribute']);
        $this->assertCount(1, $result);
    }
}
