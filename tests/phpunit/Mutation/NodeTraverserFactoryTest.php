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

<<<<<<< HEAD:tests/phpunit/Mutation/NodeTraverserFactoryTest.php
use function array_map;
use Infection\Mutation\NodeTraverserFactory;
use Infection\Visitor\FullyQualifiedClassNameVisitor;
use Infection\Visitor\NotMutableIgnoreVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\ReflectionVisitor;
=======
use Infection\Mutation\PriorityNodeTraverser;
use InvalidArgumentException;
use PhpParser\NodeVisitor;
>>>>>>> upstream/master:tests/phpunit/Mutation/PriorityNodeTraverserTest.php
use PhpParser\NodeVisitorAbstract;
use PHPUnit\Framework\TestCase;

final class NodeTraverserFactoryTest extends TestCase
{
<<<<<<< HEAD:tests/phpunit/Mutation/NodeTraverserFactoryTest.php
    /**
     * @var NodeTraverserFactory
     */
    private $traverserFactory;
=======
    public function test_it_sorts_visitors_by_priorities(): void
    {
        $traverser = new PriorityNodeTraverser();

        $callOrder = [];

        $traverser->addVisitor($this->createVisitor($callOrder, 20), 20);
        $traverser->addVisitor($this->createVisitor($callOrder, 30), 30);
        $traverser->addVisitor($this->createVisitor($callOrder, 5), 5);
        $traverser->addVisitor($this->createVisitor($callOrder, 10), 10);
>>>>>>> upstream/master:tests/phpunit/Mutation/PriorityNodeTraverserTest.php

    protected function setUp(): void
    {
        $this->traverserFactory = new NodeTraverserFactory();
    }

<<<<<<< HEAD:tests/phpunit/Mutation/NodeTraverserFactoryTest.php
    public function test_it_can_create_a_traverser(): void
=======
    public function test_it_does_not_allow_duplicated_priorities(): void
>>>>>>> upstream/master:tests/phpunit/Mutation/PriorityNodeTraverserTest.php
    {
        $traverser = $this->traverserFactory->create([]);

        $visitors = array_map('get_class', $traverser->getVisitors());

        $this->assertSame(
            [
                50 => NotMutableIgnoreVisitor::class,
                40 => ParentConnectorVisitor::class,
                30 => FullyQualifiedClassNameVisitor::class,
                20 => ReflectionVisitor::class,
            ],
            $visitors
        );
    }

    public function test_it_can_create_a_traverser_with_extra_visitors(): void
    {
        $traverser = $this->traverserFactory->create([
            51 => new NodeVisitorA(),
            100 => new NodeVisitorB(),
        ]);

        $visitors = array_map('get_class', $traverser->getVisitors());

        $this->assertSame(
            [
                100 => NodeVisitorB::class,
                51 => NodeVisitorA::class,
                50 => NotMutableIgnoreVisitor::class,
                40 => ParentConnectorVisitor::class,
                30 => FullyQualifiedClassNameVisitor::class,
                20 => ReflectionVisitor::class,
            ],
            $visitors
        );
    }
}

final class NodeVisitorA extends NodeVisitorAbstract
{
}
final class NodeVisitorB extends NodeVisitorAbstract
{
}
