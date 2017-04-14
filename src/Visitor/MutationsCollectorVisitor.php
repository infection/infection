<?php

declare(strict_types=1);

namespace Infection\Visitor;

use Infection\Mutation;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class MutationsCollectorVisitor extends NodeVisitorAbstract
{
    private $mutators = [];
    private $mutations = [];

    /**
     * @var string
     */
    private $filePath;

    public function __construct(array $mutators, string $filePath)
    {
        $this->mutators = $mutators;
        $this->filePath = $filePath;
    }

    public function leaveNode(Node $node)
    {
        if (! $node->getAttribute(InsideFunctionDetectorVisitor::IS_INSIDE_FUNCTION_KEY)) {
            return;
        }

        foreach ($this->mutators as $mutator) {
            if ($mutator->shouldMutate($node)) {
                $this->mutations[] = new Mutation(
                    $this->filePath,
                    $mutator,
                    $node->getAttributes()
                );
            }
        }
    }

    /**
     * @return array
     */
    public function getMutations(): array
    {
        return $this->mutations;
    }
}