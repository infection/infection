<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Visitor;

use Infection\Mutation;
use Infection\Mutator\Util\Mutator;
use Infection\TestFramework\Coverage\CodeCoverageData;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class MutationsCollectorVisitor extends NodeVisitorAbstract
{
    /**
     * @var Mutator[]
     */
    private $mutators = [];

    /**
     * @var Mutation[]
     */
    private $mutations = [];

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Node[]
     */
    private $fileAst;

    /**
     * @var CodeCoverageData
     */
    private $codeCoverageData;
    /**
     * @var bool
     */
    private $onlyCovered;

    public function __construct(
        array $mutators,
        string $filePath,
        array $fileAst,
        CodeCoverageData $codeCoverageData,
        bool $onlyCovered
    ) {
        $this->mutators = $mutators;
        $this->filePath = $filePath;
        $this->fileAst = $fileAst;
        $this->codeCoverageData = $codeCoverageData;
        $this->onlyCovered = $onlyCovered;
    }

    public function leaveNode(Node $node)
    {
        foreach ($this->mutators as $mutator) {
            if (!$mutator->shouldMutate($node)) {
                continue;
            }

            $isOnFunctionSignature = $node->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false);

            if (!$isOnFunctionSignature) {
                if (!$node->getAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY)) {
                    continue;
                }
            }

            if ($reflectionClass = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY, false)) {
                if ($mutator->isIgnored(
                    $reflectionClass->getName(),
                    $node->getAttribute(ReflectionVisitor::FUNCTION_NAME, '')
                )) {
                    continue;
                }
            }

            $isCoveredByTest = $this->isCoveredByTest($isOnFunctionSignature, $node);

            if ($this->onlyCovered && !$isCoveredByTest) {
                continue;
            }

            $this->mutations[] = new Mutation(
                $this->filePath,
                $this->fileAst,
                $mutator,
                $node->getAttributes(),
                get_class($node),
                $isOnFunctionSignature,
                $isCoveredByTest
            );
        }
    }

    private function isCoveredByTest(bool $isOnFunctionSignature, Node $node): bool
    {
        if ($isOnFunctionSignature) {
            return $this->codeCoverageData->hasExecutedMethodOnLine($this->filePath, $node->getLine());
        }

        return $this->codeCoverageData->hasTestsOnLine($this->filePath, $node->getLine());
    }

    /**
     * @return Mutation[]
     */
    public function getMutations(): array
    {
        return $this->mutations;
    }
}
