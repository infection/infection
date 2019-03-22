<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Mutator\Util\BaseMutator;
use PhpParser\Node;

class SimpleMutation
{
    /**
     * @var BaseMutator
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

    public function __construct(array $originalFileAst, BaseMutator $mutator, $mutatedNode)
    {
        $this->originalFileAst = $originalFileAst;
        $this->mutator = $mutator;
        $this->mutatedNode = $mutatedNode;
    }

    public function getMutator(): BaseMutator
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
