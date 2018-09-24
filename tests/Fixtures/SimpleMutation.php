<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

class SimpleMutation
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
     * @var Node
     */
    private $mutatedNode;

    public function __construct(array $originalFileAst, Mutator $mutator, $mutatedNode)
    {
        $this->originalFileAst = $originalFileAst;
        $this->mutator = $mutator;
        $this->mutatedNode = $mutatedNode;
    }

    public function getMutator(): Mutator
    {
        return $this->mutator;
    }

    public function getOriginalFileAst(): array
    {
        return $this->originalFileAst;
    }

    public function getMutatedNode()
    {
        return $this->mutatedNode;
    }
}
