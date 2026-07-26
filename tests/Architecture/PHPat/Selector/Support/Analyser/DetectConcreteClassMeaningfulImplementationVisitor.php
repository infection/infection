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

use Infection\Tests\UnsupportedMethod;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Throw_ as ThrowExpression;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use function spl_object_id;

final class DetectConcreteClassMeaningfulImplementationVisitor extends NodeVisitorAbstract
{
    private ?Class_ $targetClass = null;

    private bool $foundTargetClass = false;

    private bool $isInsideTargetClass = false;

    /**
     * @var array<int, true>
     */
    private array $targetClassStatementIds = [];

    private ?ClassMethod $currentMethod = null;

    private bool $isInsideCurrentMethodStatement = false;

    /**
     * @var array<int, true>
     */
    private array $currentMethodStatementIds = [];

    private bool $meaningfulImplementation = false;

    public function __construct(
        private readonly string $targetShortClassName,
    ) {
    }

    public function enterNode(Node $node): ?int
    {
        if (!$this->foundTargetClass) {
            if ($node instanceof Class_ && $this->isTargetClass($node)) {
                $this->foundTargetClass = true;
                $this->isInsideTargetClass = true;
                $this->targetClass = $node;
                $this->targetClassStatementIds = self::getStatementIds($node);

                return null;
            }

            return null;
        }

        if (!$this->isInsideTargetClass) {
            return null;
        }

        if ($this->currentMethod !== null) {
            if (isset($this->currentMethodStatementIds[spl_object_id($node)])) {
                $this->isInsideCurrentMethodStatement = true;

                return null;
            }

            return $this->detectMeaningfulMethodImplementation($node);
        }

        if (!$node instanceof ClassMethod || !isset($this->targetClassStatementIds[spl_object_id($node)])) {
            return null;
        }

        $this->currentMethod = $node;
        $this->currentMethodStatementIds = self::getMethodStatementIds($node);

        return null;
    }

    public function leaveNode(Node $node): ?int
    {
        if (!$this->isInsideTargetClass) {
            return null;
        }

        if ($node === $this->targetClass) {
            $this->isInsideTargetClass = false;
            $this->targetClassStatementIds = [];

            return null;
        }

        if ($this->currentMethod !== null) {
            if ($node === $this->currentMethod) {
                $this->currentMethod = null;
                $this->isInsideCurrentMethodStatement = false;
                $this->currentMethodStatementIds = [];

                return null;
            }

            if (isset($this->currentMethodStatementIds[spl_object_id($node)])) {
                $this->isInsideCurrentMethodStatement = false;

                return null;
            }
        }

        return null;
    }

    public function hasMeaningfulImplementation(): bool
    {
        return $this->meaningfulImplementation;
    }

    private function isTargetClass(Class_ $node): bool
    {
        return !$node->isAnonymous()
            && $node->name?->toString() === $this->targetShortClassName;
    }

    /**
     * @return array<int, true>
     */
    private static function getStatementIds(Class_ $class): array
    {
        $statementIds = [];

        foreach ($class->stmts as $statement) {
            $statementIds[spl_object_id($statement)] = true;
        }

        return $statementIds;
    }

    /**
     * @return array<int, true>
     */
    private static function getMethodStatementIds(ClassMethod $method): array
    {
        if ($method->stmts === null) {
            return [];
        }

        $statementIds = [];

        foreach ($method->stmts as $statement) {
            if ($statement instanceof Nop) {
                continue;
            }

            $statementIds[spl_object_id($statement)] = true;
        }

        return $statementIds;
    }

    private function detectMeaningfulMethodImplementation(Node $node): ?int
    {
        if (!$this->isInsideCurrentMethodStatement) {
            return null;
        }

        if ($node instanceof ThrowExpression || self::isUnsupportedMethodFactoryCall($node)) {
            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }

        if (!$node instanceof Node\Expr || self::isTrivialExpression($node)) {
            return null;
        }

        $this->meaningfulImplementation = true;

        return null;
    }

    private static function isTrivialExpression(Node\Expr $expression): bool
    {
        return $expression instanceof Node\Expr\Array_
            || $expression instanceof Node\Expr\ClassConstFetch
            || $expression instanceof Node\Expr\ConstFetch
            || $expression instanceof Node\Scalar;
    }

    /**
     * @see UnsupportedMethod
     */
    private static function isUnsupportedMethodFactoryCall(Node $node): bool
    {
        if (!$node instanceof StaticCall) {
            return false;
        }

        if (!$node->class instanceof Node\Name) {
            return false;
        }

        return $node->class->getLast() === 'UnsupportedMethod'
            && $node->name instanceof Node\Identifier
            && $node->name->toString() === 'method';
    }
}
