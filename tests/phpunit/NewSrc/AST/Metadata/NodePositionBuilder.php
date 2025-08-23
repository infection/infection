<?php

declare(strict_types=1);

namespace Infection\Tests\NewSrc\AST\Metadata;

use newSrc\AST\Metadata\NodePosition;

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
