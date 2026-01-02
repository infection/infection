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

namespace Infection\Mutation;

use Infection\Ast\Ast;
use Infection\Mutator\Mutator;
use Infection\Mutator\NodeMutationGenerator;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\UnparsableFile;
use Infection\PhpParser\Visitor\MutationCollectorVisitor;
use Infection\Source\Exception\NoSourceFound;

/**
 * TODO: this was the previous FileMutationGenerator. Renamed it to MutationGenerator as the
 *   latter no longer really makes sense. The only thing it did was dispatching the events
 *   as it was starting the loop, but now the loop is started earlier.
 *   I'll have to review the events eventually as the sequence is at which the events are dispatched
 *   is no longer the same.
 *
 * @internal
 */
final readonly class MutationGenerator
{
    /**
     * @param Mutator[] $mutators
     */
    public function __construct(
        private array $mutators,
        private NodeTraverserFactory $traverserFactory,
    ) {
    }

    /**
     * @throws NoSourceFound
     * @throws UnparsableFile
     *
     * @return iterable<Mutation>
     */
    public function generate(Ast $ast): iterable
    {
        $visitor = new MutationCollectorVisitor(
            new NodeMutationGenerator(
                mutators: $this->mutators,
                filePath: $ast->trace->getRealPath(),
                fileNodes: $ast->initialStatements,
                originalFileTokens: $ast->originalFileTokens,
                originalFileContent: $ast->trace->getSourceFileInfo()->getContents(),
            ),
        );

        $this->traverserFactory
            ->createSecondTraverser($visitor)
            ->traverse($ast->nodes);

        // TODO: in the future this is where we would apply the strategy selection.
        yield from $visitor->getMutations();
    }
}
