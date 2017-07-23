<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Visitor;

use Infection\Mutation;
use Infection\TestFramework\Coverage\CodeCoverageData;
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
    /**
     * @var CodeCoverageData
     */
    private $codeCoverageData;
    /**
     * @var bool
     */
    private $onlyCovered;

    public function __construct(array $mutators, string $filePath, CodeCoverageData $codeCoverageData, bool $onlyCovered)
    {
        $this->mutators = $mutators;
        $this->filePath = $filePath;
        $this->codeCoverageData = $codeCoverageData;
        $this->onlyCovered = $onlyCovered;
    }

    public function leaveNode(Node $node)
    {
        if (!$node->getAttribute(InsideFunctionDetectorVisitor::IS_INSIDE_FUNCTION_KEY)) {
            return;
        }

        if ($this->onlyCovered && !$this->codeCoverageData->hasTestsOnLine($this->filePath, $node->getLine())) {
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
