<?php

declare(strict_types=1);

namespace Infection\Tests\NewSrc\PhpParser\NodeDumper;

use PhpParser\Node;
use function func_get_args;

final class NodeDumperScenario
{
    /**
     * @param list<Node>|Node $node
     */
    public function __construct(
        public array|Node|string $node,
        public string $expected = '',
        // It should have the same defaults as NodeDumper
        public bool $dumpProperties = false,
        public bool $dumpComments = false,
        public bool $dumpPositions = false,
        public bool $dumpOtherAttributes = false,
    ) {
    }

    /**
     * @param list<Node>|Node $node
     */
    public static function forNode(array|Node $node): self
    {
        return new self($node);
    }

    /**
     * @param list<Node>|Node $node
     */
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

    public function withExpected(string $expected): self
    {
        $clone = clone $this;
        $clone->expected = $expected;

        return $clone;
    }

    public function build(): array
    {
        return [$this];
    }
}
