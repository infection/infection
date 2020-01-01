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

namespace Infection\Mutator\Number;

use Infection\Mutator\Classification;
use Infection\Mutator\Definition;
use PhpParser\Node;

/**
 * @internal
 *
 * TODO: review the remedies and the others
 * TODO: what about -1 or -1.?
 */
final class OneZeroFloat extends AbstractNumberMutator
{
    public static function getDefinition(): ?Definition
    {
        return new Definition(
            <<<'TXT'
Replaces a zero float value (`0.0`) by a non-zero float value (`1.0`) and vice-versa.
TXT
            ,
            Classification::ORTHOGONAL_REPLACEMENT,
            null
        );
    }

    /**
     * @param Node&Node\Scalar\DNumber $node
     *
     * @return Node\Scalar\DNumber
     */
    public function mutate(Node $node)
    {
        if ($node->value === 0.0) {
            return new Node\Scalar\DNumber(1.0);
        }

        return new Node\Scalar\DNumber(0.0);
    }

    protected function mutatesNode(Node $node): bool
    {
        return
            $node instanceof Node\Scalar\DNumber
            && ($node->value === 0.0 || $node->value === 1.0)
            && !$this->isPartOfSizeComparison($node);
    }
}
