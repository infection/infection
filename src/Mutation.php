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
     * @var array
     */
    private $attributes;

    public function __construct(Mutator $mutator, array $attributes)
    {

        $this->mutator = $mutator;
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
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
