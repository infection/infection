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

namespace Infection\Tests\Ast\Metadata;

use Infection\Ast\Metadata\NodePosition;

final class NodePositionBuilder
{
    public function __construct(
        public int $startLine,
        public int $startTokenPosition,
        public int $endLine,
        public int $endTokenPosition,
    ) {
    }

    public static function from(NodePosition $nodePosition)
    {
        return new self(
            $nodePosition->startLine,
            $nodePosition->startTokenPosition,
            $nodePosition->endLine,
            $nodePosition->endTokenPosition,
        );
    }

    public static function singleLineWithTestData(): self
    {
        return new self(
            startLine: 5,
            startTokenPosition: 3,
            endLine: 5,
            endTokenPosition: 10,
        );
    }

    public static function multiLineWithTestData(): self
    {
        return new self(
            startLine: 5,
            startTokenPosition: 3,
            endLine: 8,
            endTokenPosition: 10,
        );
    }

    public function withStartLine(int $startLine): self
    {
        $clone = clone $this;
        $clone->startLine = $startLine;

        return $clone;
    }

    public function withStartTokenPosition(int $startTokenPosition): self
    {
        $clone = clone $this;
        $clone->startTokenPosition = $startTokenPosition;

        return $clone;
    }

    public function withEndLine(int $endLine): self
    {
        $clone = clone $this;
        $clone->endLine = $endLine;

        return $clone;
    }

    public function withEndTokenPosition(int $endTokenPosition): self
    {
        $clone = clone $this;
        $clone->endTokenPosition = $endTokenPosition;

        return $clone;
    }

    public function build(): NodePosition
    {
        return new NodePosition(
            $this->startLine,
            $this->startTokenPosition,
            $this->endLine,
            $this->endTokenPosition,
        );
    }
}
