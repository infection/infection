<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Operator;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ParentConnector;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class Continue_ implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a continue statement (`continue`) with its counterpart break statement (`break`).
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
foreach ($collection as $item) {
    if ($condition) {
-       continue;
+       break;
    }
}
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Stmt\Break_());
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Stmt\Continue_) {
            return \false;
        }
        $parentNode = ParentConnector::findParent($node);
        return !$parentNode instanceof Node\Stmt\Case_;
    }
}
