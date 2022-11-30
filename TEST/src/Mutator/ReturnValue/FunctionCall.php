<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\ReturnValue;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\Mutator\Util\AbstractValueToNullReturnValue;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@extends
*/
final class FunctionCall extends AbstractValueToNullReturnValue
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a returned evaluated function with `null` instead. The function evaluation statement is kept
in order to preserve potential side effects. For example:

```php
class X {
    function foo()
    {
        return bar();
    }
}
```

Will be mutated to:

```php
class X {
    function foo()
    {
        bar();
        return null;
    }
}
```

TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
class X {
    function foo()
    {
-        return bar();
+        bar();
+        return null;
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
        $expr = $node->expr;
        (yield [new Node\Stmt\Expression($expr), new Node\Stmt\Return_(new Node\Expr\ConstFetch(new Node\Name('null')))]);
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return \false;
        }
        if (!$node->expr instanceof Node\Expr\FuncCall) {
            return \false;
        }
        return $this->isNullReturnValueAllowed($node);
    }
}
