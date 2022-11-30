<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class SyntaxError implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Replaces a `$this` with `false` to produce a syntax error. Internal usage only.', MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
class X {
    function foo()
    {
-        $this->method();
+        $->method();
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
        (yield new Node\Expr\ConstFetch(new Node\Name('$')));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\Variable && $node->name === 'this';
    }
}
