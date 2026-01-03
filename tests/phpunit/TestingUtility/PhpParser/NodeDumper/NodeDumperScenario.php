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

namespace Infection\Tests\TestingUtility\PhpParser\NodeDumper;

use Exception;
use PhpParser\Node;

final class NodeDumperScenario
{
    /**
     * @param list<Node>|Node $node
     */
    public function __construct(
        public array|Node|string $node,
        public string|Exception $expected = '',
        // It should have the same defaults as NodeDumper
        public bool $dumpProperties = false,
        public bool $dumpComments = false,
        public bool $dumpPositions = false,
        public bool $dumpOtherAttributes = false,
        public bool $onlyVisitedNodes = true,
    ) {
    }

    /**
     * @param list<Node>|Node $node
     */
    public static function forNode(array|Node $node): self
    {
        return new self($node);
    }

    public static function forCode(string $code): self
    {
        return new self($code);
    }

    public function withDumpProperties(): self
    {
        $clone = clone $this;
        $clone->dumpProperties = true;

        return $clone;
    }

    public function withDumpComments(): self
    {
        $clone = clone $this;
        $clone->dumpComments = true;

        return $clone;
    }

    public function withDumpPositions(): self
    {
        $clone = clone $this;
        $clone->dumpPositions = true;

        return $clone;
    }

    public function withDumpOtherAttributes(): self
    {
        $clone = clone $this;
        $clone->dumpOtherAttributes = true;

        return $clone;
    }

    public function withShowAllNodes(): self
    {
        $clone = clone $this;
        $clone->onlyVisitedNodes = false;

        return $clone;
    }

    public function withExpected(string|Exception $expected): self
    {
        $clone = clone $this;
        $clone->expected = $expected;

        return $clone;
    }

    /**
     * @return array{NodeDumperScenario}
     */
    public function build(): array
    {
        return [$this];
    }
}
