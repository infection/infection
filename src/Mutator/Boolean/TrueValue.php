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

namespace Infection\Mutator\Boolean;

use function array_flip;
use function array_key_exists;
use Infection\Mutator\ConfigurableMutator;
use Infection\Mutator\Definition;
use Infection\Mutator\GetConfigClassName;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\MutatorCategory;
use Infection\PhpParser\Visitor\ParentConnector;
use PhpParser\Node;

/**
 * @internal
 *
 * @implements ConfigurableMutator<Node\Expr\ConstFetch>
 */
final class TrueValue implements ConfigurableMutator
{
    use GetConfigClassName;
    use GetMutatorName;

    /**
     * @var array<string, int>
     */
    private readonly array $allowedFunctions;

    public function __construct(TrueValueConfig $config)
    {
        $this->allowedFunctions = array_flip($config->getAllowedFunctions());
    }

    public static function getDefinition(): Definition
    {
        return new Definition(
            'Replaces a boolean literal (`true`) with its opposite value (`false`). ',
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
                - $a = true;
                + $a = false;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Expr\ConstFetch>
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Expr\ConstFetch(new Node\Name('false'));
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Expr\ConstFetch) {
            return false;
        }

        if ($node->name->toLowerString() !== 'true') {
            return false;
        }

        $parentNode = ParentConnector::findParent($node);
        $grandParentNode = $parentNode !== null ? ParentConnector::findParent($parentNode) : null;

        if ($parentNode instanceof Node\Stmt\Switch_) {
            return false;
        }

        if (!$grandParentNode instanceof Node\Expr\FuncCall || !$grandParentNode->name instanceof Node\Name) {
            return true;
        }

        $functionName = $grandParentNode->name->toLowerString();

        return array_key_exists($functionName, $this->allowedFunctions);
    }
}
