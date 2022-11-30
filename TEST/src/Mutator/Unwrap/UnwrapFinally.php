<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class UnwrapFinally implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Replaces `try-catch-finally` block with try-catch or try-finally with simple statements.', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
$check = true;
- try {
-     $callback();
- }
- } finally {
-     $check = false;
- }
+ $callback();
+ $check = false
DIFF
);
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Stmt\TryCatch && $node->finally !== null;
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        if ($node->catches === []) {
            (yield [...$node->stmts, ...$node->finally->stmts ?? []]);
            return;
        }
        (yield [new Node\Stmt\TryCatch($node->stmts, $node->catches, null, $node->getAttributes()), ...$node->finally->stmts ?? []]);
    }
}
