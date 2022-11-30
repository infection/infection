<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use function count;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\Differ\FilesDiffChangedLines;
use _HumbugBox9658796bb9f0\Infection\Logger\GitHub\GitDiffFileProvider;
use _HumbugBox9658796bb9f0\Infection\Mutation\Mutation;
use _HumbugBox9658796bb9f0\Infection\PhpParser\MutatedNode;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ReflectionVisitor;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\LineRangeCalculator;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\Trace;
use function iterator_to_array;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use Throwable;
use Traversable;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class NodeMutationGenerator
{
    private array $mutators;
    private Node $currentNode;
    private ?array $testsMemoized = null;
    private ?bool $isOnFunctionSignatureMemoized = null;
    private ?bool $isInsideFunctionMemoized = null;
    public function __construct(array $mutators, private string $filePath, private array $fileNodes, private Trace $trace, private bool $onlyCovered, private bool $isForGitDiffLines, private ?string $gitDiffBase, private LineRangeCalculator $lineRangeCalculator, private FilesDiffChangedLines $filesDiffChangedLines)
    {
        Assert::allIsInstanceOf($mutators, Mutator::class);
        $this->mutators = $mutators;
    }
    public function generate(Node $node) : iterable
    {
        $this->currentNode = $node;
        $this->testsMemoized = null;
        $this->isOnFunctionSignatureMemoized = null;
        $this->isInsideFunctionMemoized = null;
        if (!$this->isOnFunctionSignature() && !$this->isInsideFunction()) {
            return;
        }
        if ($this->isForGitDiffLines && !$this->filesDiffChangedLines->contains($this->filePath, $node->getStartLine(), $node->getEndLine(), $this->gitDiffBase ?? GitDiffFileProvider::DEFAULT_BASE)) {
            return;
        }
        foreach ($this->mutators as $mutator) {
            yield from $this->generateForMutator($node, $mutator);
        }
    }
    private function generateForMutator(Node $node, Mutator $mutator) : iterable
    {
        try {
            if (!$mutator->canMutate($node)) {
                return;
            }
        } catch (Throwable $throwable) {
            throw InvalidMutator::create($this->filePath, $mutator->getName(), $throwable);
        }
        $tests = $this->getAllTestsForCurrentNode();
        if ($this->onlyCovered && count($tests) === 0) {
            return;
        }
        $mutationByMutatorIndex = 0;
        foreach ($mutator->mutate($node) as $mutatedNode) {
            (yield new Mutation($this->filePath, $this->fileNodes, $mutator->getName(), $node->getAttributes(), $node::class, MutatedNode::wrap($mutatedNode), $mutationByMutatorIndex, $tests));
            ++$mutationByMutatorIndex;
        }
    }
    private function isOnFunctionSignature() : bool
    {
        return $this->isOnFunctionSignatureMemoized ?? ($this->isOnFunctionSignatureMemoized = $this->currentNode->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, \false));
    }
    private function isInsideFunction() : bool
    {
        return $this->isInsideFunctionMemoized ?? ($this->isInsideFunctionMemoized = $this->currentNode->getAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY, \false));
    }
    private function getAllTestsForCurrentNode() : array
    {
        if ($this->testsMemoized !== null) {
            return $this->testsMemoized;
        }
        $testsMemoized = $this->trace->getAllTestsForMutation($this->lineRangeCalculator->calculateRange($this->currentNode), $this->isOnFunctionSignature());
        if ($testsMemoized instanceof Traversable) {
            $testsMemoized = iterator_to_array($testsMemoized, \false);
        }
        return $this->testsMemoized = $testsMemoized;
    }
}
