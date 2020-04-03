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

use Infection\PhpParser\Visitor\ParentConnector;
use InvalidArgumentException;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\TestCase;

final class ParentConnectorTest extends TestCase
{
    public function test_it_can_provide_the_node_parent(): void
    {
        $parent = new Nop();

        $node = new Nop(['parent' => $parent]);

        $this->assertSame($parent, ParentConnector::getParent($node));
        $this->assertSame($parent, ParentConnector::findParent($node));
    }

    public function test_it_can_look_for_the_node_parent(): void
    {
        $parent = new Nop();

        $node1 = new Nop(['parent' => $parent]);
        $node2 = new Nop(['parent' => null]);
        $node3 = new Nop();

        $this->assertSame($parent, ParentConnector::findParent($node1));
        $this->assertNull(ParentConnector::findParent($node2));
        $this->assertNull(ParentConnector::findParent($node3));
    }

    public function test_it_cannot_provide_the_node_parent_if_has_not_be_set_yet(): void
    {
        $node = new Nop();

        $this->expectException(InvalidArgumentException::class);

        // We are not interested in a more helpful message here since it would be the result of
        // a misconfiguration on our part rather than a user one. Plus this would require some
        // extra processing on a part which is quite a hot path.

        ParentConnector::getParent($node);
    }

    public function test_it_can_set_a_node_parent(): void
    {
        $parent = new Nop();
        $node = new Nop();

        ParentConnector::setParent($node, $parent);

        $this->assertSame($parent, ParentConnector::getParent($node));

        ParentConnector::setParent($node, null);

        $this->assertNull(ParentConnector::findParent($node));
    }
}
