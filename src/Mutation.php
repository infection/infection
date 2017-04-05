<?php

declare(strict_types=1);

namespace Infection;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class Mutation
{
    /**
     * @var Mutator
     */
    private $mutator;
    /**
     * @var mixed
     */
    private $mutatedNode;
    /**
     * @var array
     */
    private $attributes;

    public function __construct(Mutator $mutator, $mutatedNode, array $attributes)
    {

        $this->mutator = $mutator;
        $this->mutatedNode = $mutatedNode;
        $this->attributes = $attributes;
    }

    /**
     * @return Mutator
     */
    public function getMutator(): Mutator
    {
        return $this->mutator;
    }

    /**
     * @return mixed
     */
    public function getMutatedNode()
    {
        return $this->mutatedNode;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
