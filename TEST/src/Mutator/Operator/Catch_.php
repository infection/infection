<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Operator;

use function count;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class Catch_ implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Removes exception types in `catch` block.', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
try {
    $fn();
- } catch (\Exception | \DomainException $e) {
+ } catch (\Exception $e) {
    throw $e;
}
DIFF
);
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Stmt\Catch_ && count($node->types) > 1;
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        foreach ($node->types as $i => $type) {
            $types = $node->types;
            unset($types[$i]);
            (yield new Node\Stmt\Catch_($types, $node->var, $node->stmts, $node->getAttributes()));
        }
    }
}
