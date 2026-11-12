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

namespace Infection\Tests\Architecture\PHPat\Selector\Support\Analyser;

use Infection\Container\Container;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;
use function strcasecmp;

final class ContainerCreateUsageVisitor extends NodeVisitorAbstract
{
    private ?ClassLike $targetClass = null;

    private bool $callsContainerCreate = false;

    public function __construct(private readonly string $className)
    {
    }

    public function enterNode(Node $node): ?int
    {
        if (
            $node instanceof ClassLike
            && $node->namespacedName?->toString() === $this->className
        ) {
            $this->targetClass = $node;
        }

        if (
            $this->targetClass === null
            || !$node instanceof StaticCall
            || !$node->class instanceof Name
            || !$node->name instanceof Identifier
        ) {
            return null;
        }

        $className = $node->class->getAttribute('resolvedName', $node->class);

        if (
            $className instanceof Name
            && strcasecmp($className->toString(), Container::class) === 0
            && strcasecmp($node->name->toString(), 'create') === 0
        ) {
            $this->callsContainerCreate = true;
        }

        return null;
    }

    public function leaveNode(Node $node): ?int
    {
        if ($node === $this->targetClass) {
            $this->targetClass = null;
        }

        return null;
    }

    public function callsContainerCreate(): bool
    {
        return $this->callsContainerCreate;
    }
}
