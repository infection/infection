<?php

declare(strict_types=1);

namespace newSrc\Engine;

use newSrc\Mutagenesis\Mutation;
use newSrc\MutationAnalyzer\MutantExecutionResult;
use PhpParser\Node;
use SplFileInfo;

final class Envelope
{
    /**
     * @param Node[]|null $ast
     */
    public function __construct(
        public readonly SplFileInfo $file,
        private array|null $ast = null,
        private Mutation|null $mutation = null,
        private MutantExecutionResult|null $executionResult = null,
    ) {}

    public static function create(SplFileInfo $file): self
    {
        return new self($file);
    }

    /**
     * @param Node[] $ast
     */
    public function withAst(array $ast): self
    {
        $this->ast = $ast;

        return $this;
    }

    public function forMutation(Mutation $mutation): self {
        $clone = clone $this;
        $clone->mutation = $mutation;

        return $clone;
    }

    public function withResult(MutantExecutionResult $executionResult): self
    {
        $this->executionResult = $executionResult;

        return $this;
    }
}
