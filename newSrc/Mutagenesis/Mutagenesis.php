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

namespace newSrc\Mutagenesis;

// Based on the current MutationGenerator
use newSrc\Mutagenesis\Strategy\Strategy;
use newSrc\Mutator\MutatorRegistry;
use PhpParser\Node;
use PhpParser\NodeTraverser;

final class Mutagenesis
{
    public function __construct(
        private MutatorRegistry $mutatorRegistry,
        private Strategy $strategy,
    ) {
    }

    /**
     * @param Node[] $nodes
     *
     * @return iterable<Mutation>
     */
    public function generate(array $nodes): iterable
    {
        $visitor = new MutagenesisVisitor($this->mutatorRegistry);

        $traverser = new NodeTraverser($visitor);
        $traverser->traverse($nodes);

        // TODO: the logic here is likely gonna be complex and needs experimenting.
        // The idea is that:
        // - mutations are generated, one by one
        // - depending on the strategy employed, _more_ may be requested, but maybe not.
        // - the ones yielded by the strategy applied are evaluated.
        // - this means:
        //      - We cannot know ahead of time the number of mutations issued.
        //      - we do not generate all mutations at once
        // Note certain that the design is good enough: need to check how can a strategy know if it needs
        // to yield more. Maybe need to be injected a service that can be mutated downstream.
        yield from $this->strategy->apply($visitor->getPotentialMutations());
    }
}
