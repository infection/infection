<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Loop;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class For_ implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces the looping condition of a `for` block statement preventing any statement within the block
to be executed. For example:

```php`
for ($i=0; $i<10; $i++) {
    // ...
}
```

Will be mutated to:

```php
for ($i=0; false; $i++) {
    // ...
}
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- for ($i=0; $i<10; $i++) {
+ for ($i=0; false; $i++) {
      // ...
}
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Stmt\For_(['init' => $node->init, 'cond' => [new Node\Expr\ConstFetch(new Node\Name('false'))], 'loop' => $node->loop, 'stmts' => $node->stmts], $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Stmt\For_;
    }
}
