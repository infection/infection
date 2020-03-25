<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Mutation\Mutation;
use Infection\Mutator\Mutator;
use Infection\PhpParser\MutatedNode;
use PhpParser\Node;

class SimpleMutation extends Mutation
{
    /**
     * @var Mutator
     */
    private $mutator;

    /**
     * @var Node[]
     */
    private $originalFileAst;

    /**
     * @var MutatedNode
     */
    private $mutatedNode;
    /**
     * @var array
     */
    private $originalStartingLine;
    /**
     * @var string
     */
    private $mutatedNodeClass;

    public function __construct(
        array $originalFileAst,
        Mutator $mutator,
        $mutatedNode,
        array $attributes,
        string $mutatedNodeClass
    ) {
        $this->originalFileAst = $originalFileAst;
        $this->mutator = $mutator;
        $this->mutatedNode = $mutatedNode;
        $this->originalStartingLine = $attributes;
        $this->mutatedNodeClass = $mutatedNodeClass;
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

    public function getOriginalStartingLine(): array
    {
        return $this->originalStartingLine;
    }

    public function getMutatedNodeClass(): string
    {
        return $this->mutatedNodeClass;
    }
}
