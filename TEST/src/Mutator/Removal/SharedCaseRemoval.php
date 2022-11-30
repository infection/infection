<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Removal;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class SharedCaseRemoval implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Removes `case`s from `switch`.', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
switch ($x) {
-   case 1:
    case 2:
        fooBar();
        break;
    default:
        baz();
}
DIFF
);
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Stmt\Switch_) {
            return \false;
        }
        foreach ($node->cases as $case) {
            if ($case->stmts === []) {
                return \true;
            }
        }
        return \false;
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        $previousWasEmpty = \false;
        foreach ($node->cases as $i => $case) {
            if ($case->stmts === []) {
                $previousWasEmpty = \true;
                $cases = $node->cases;
                unset($cases[$i]);
                (yield new Node\Stmt\Switch_($node->cond, $cases, $node->getAttributes()));
                continue;
            }
            if ($previousWasEmpty) {
                $previousWasEmpty = \false;
                $cases = $node->cases;
                unset($cases[$i]);
                $lastCase = $cases[$i - 1];
                $cases[$i - 1] = new Node\Stmt\Case_($lastCase->cond, $case->stmts, $lastCase->getAttributes());
                (yield new Node\Stmt\Switch_($node->cond, $cases, $node->getAttributes()));
            }
        }
    }
}
