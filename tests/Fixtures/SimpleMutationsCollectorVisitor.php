<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class SimpleMutationsCollectorVisitor extends NodeVisitorAbstract
{
    /**
     * @var Mutator[]
     */
    private $mutator;

    /**
     * @var SimpleMutation[]
     */
    private $mutations = [];

    /**
     * @var Node[]
     */
    private $fileAst;

    public function __construct(Mutator $mutator, array $fileAst)
    {
        $this->mutator = $mutator;
        $this->fileAst = $fileAst;
    }

    public function leaveNode(Node $node)
    {
        if (!$this->mutator->shouldMutate($node)) {
            return;
        }

        foreach($this->mutator->mutate($node) as $mutatedNode) {
            $this->mutations[] = new SimpleMutation(
                $this->fileAst,
                $this->mutator,
                $mutatedNode
            );
        }
    }

    /**
     * @return SimpleMutation[]
     */
    public function getMutations(): array
    {
        return $this->mutations;
    }
}
