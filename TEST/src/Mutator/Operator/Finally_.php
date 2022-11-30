<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Operator;

use function count;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ParentConnector;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
/**
@implements
*/
final class Finally_ implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Removes the `finally` block.', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
try {
    // do smth
+ }
- } finally {
-
- }
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Stmt\Nop());
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Stmt\Finally_) {
            return \false;
        }
        return $this->hasAtLeastOneCatchBlock($node);
    }
    private function hasAtLeastOneCatchBlock(Node $node) : bool
    {
        $parentNode = ParentConnector::getParent($node);
        Assert::isInstanceOf($parentNode, Node\Stmt\TryCatch::class);
        return count($parentNode->catches) > 0;
    }
}
