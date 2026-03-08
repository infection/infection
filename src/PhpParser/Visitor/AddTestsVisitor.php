<?php

declare(strict_types=1);

namespace Infection\PhpParser\Visitor;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutator\Mutator;
use Infection\Source\Matcher\SourceLineMatcher;
use Infection\TestFramework\Tracing\Trace\LineRangeCalculator;
use Infection\TestFramework\Tracing\Trace\Trace;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Token;
use Traversable;
use Webmozart\Assert\Assert;
use function iterator_to_array;

final class AddTestsVisitor extends NodeVisitorAbstract
{
    private const TESTS = 'tests';

    /**
     * @param Mutator<Node>[] $mutators
     * @param Node[] $fileNodes
     * @param Token[] $originalFileTokens
     */
    public function __construct(
        array $mutators,
        private readonly string $filePath,
        private readonly array $fileNodes,
        private readonly Trace $trace,
        private readonly bool $onlyCovered,
        private readonly LineRangeCalculator $lineRangeCalculator,
        private readonly SourceLineMatcher $sourceLineMatcher,
        private readonly array $originalFileTokens,
        private readonly string $originalFileContent,
    ) {
        Assert::allIsInstanceOf($mutators, Mutator::class);

        $this->mutators = $mutators;
    }

    public function enterNode(Node $node): null
    {
        $node->setAttribute(

        );

        return null;
    }

    /**
     * @return TestLocation[]
     */
    private function getAllTestsForNode(Node $node): array
    {
        if ($this->testsMemoized !== null) {
            return $this->testsMemoized;
        }

        $testsMemoized = $this->trace->getAllTestsForMutation(
            $this->lineRangeCalculator->calculateRange($this->currentNode),
            $this->isOnFunctionSignature(),
        );

        if ($testsMemoized instanceof Traversable) {
            $testsMemoized = iterator_to_array($testsMemoized, false);
        }

        return $this->testsMemoized = $testsMemoized;
    }
}
