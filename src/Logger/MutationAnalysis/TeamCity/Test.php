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

namespace Infection\Logger\MutationAnalysis\TeamCity;

use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use function round;
use function sprintf;

/**
 * @phpstan-import-type MessageAttributes from TeamCity
 *
 * @internal
 */
final readonly class Test
{
    private const MILLISECONDS_PER_SECOND = 1000;

    public function __construct(
        public string $id,
        public string $name,
        public string $nodeId,
        public string $parentNodeId,
    ) {
    }

    public static function create(
        Mutation $mutation,
        string $parentNodeId,
    ): self {
        return new self(
            id: $mutation->getHash(),
            name: self::createName($mutation),
            // The Mutation hash is too long to be suitable to be a nodeId.
            nodeId: NodeIdFactory::create($mutation->getHash()),
            parentNodeId: $parentNodeId,
        );
    }

    /**
     * @return MessageAttributes
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'nodeId' => $this->nodeId,
        ];
    }

    /**
     * @return MessageAttributes
     */
    public function toFinishedAttributes(MutantExecutionResult $executionResult): array
    {
        return $this->toAttributes() + [
            // TODO: looks like this information is not used when the test is marked as successful or ignored :/
            'message' => self::createMutationMessage($executionResult),
            'details' => $executionResult->getMutantDiff(),
            'duration' => self::getExecutionDurationInMs($executionResult),
        ];
    }

    private static function createName(Mutation $mutation): string
    {
        return sprintf(
            '%s (%s)',
            $mutation->getMutatorClass(),
            $mutation->getHash(),
        );
    }

    private static function createMutationMessage(MutantExecutionResult $executionResult): string
    {
        return sprintf(
            <<<'MESSAGE'
                Mutator: %s
                Mutation ID: %s
                Mutation result: %s
                MESSAGE,
            $executionResult->getMutatorName(),
            $executionResult->getMutantHash(),
            $executionResult->getDetectionStatus()->value,
        );
    }

    private static function getExecutionDurationInMs(MutantExecutionResult $executionResult): string
    {
        // TODO: this duration is not correct.
        //  see: https://github.com/infection/infection/issues/2900
        return (string) round($executionResult->getProcessRuntime() * self::MILLISECONDS_PER_SECOND);
    }
}
