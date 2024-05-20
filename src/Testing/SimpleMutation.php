<?php

declare(strict_types=1);

namespace Infection\Testing;

use Infection\Mutation\Mutation;
use Infection\Mutator\Mutator;
use Infection\PhpParser\MutatedNode;
use PhpParser\Node;

class SimpleMutation extends Mutation
{
    /**
     * @param MutatedNode $mutatedNode
     */
    public function __construct(
        /**
         * @var Node[]
         */
        private readonly array $originalFileAst,
        private readonly Mutator $mutator,
        private $mutatedNode,
        private readonly array $attributes,
        private readonly string $mutatedNodeClass
    )
    {
    }

    public function getMutator(): Mutator
    {
        return $this->mutator;
    }

    public function getOriginalFileAst(): array
    {
        return $this->originalFileAst;
    }

    public function getMutatedNode(): MutatedNode
    {
        return $this->mutatedNode;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getMutatedNodeClass(): string
    {
        return $this->mutatedNodeClass;
    }
}
