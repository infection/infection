<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Removal;

use function count;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class CatchBlockRemoval implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Removes `catch` block when more than one defined in `try-catch`.', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
try {
    $callback();
- } catch (\DomainException $ex) {
-     $logger->log($ex);
} catch (\LogicException $e) {
    throw $e;
}
DIFF
);
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Stmt\TryCatch || count($node->catches) < 2) {
            return \false;
        }
        foreach ($node->catches as $catch) {
            if ($catch->stmts !== []) {
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
        foreach ($node->catches as $i => $catch) {
            if (!$this->hasAtLeastOneNonNopStatements(...$catch->stmts)) {
                continue;
            }
            $catches = $node->catches;
            unset($catches[$i]);
            (yield new Node\Stmt\TryCatch($node->stmts, $catches, $node->finally, $node->getAttributes()));
        }
    }
    private function hasAtLeastOneNonNopStatements(Node\Stmt ...$stmts) : bool
    {
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Node\Stmt\Nop) {
                return \true;
            }
        }
        return \false;
    }
}
