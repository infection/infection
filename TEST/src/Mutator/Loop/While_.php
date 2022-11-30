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
final class While_ implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces the iterable being iterated over with a `while` expression with false, preventing
any iteration within the block to be executed. For example:

```php`

$condition = true;
while ($condition) {
    // ...
}
```

Will be mutated to:

```php

$condition = true;
while (false) {
    // ...
}
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- while ($condition) {
+ while (false) {
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
        (yield new Node\Stmt\While_(new Node\Expr\ConstFetch(new Node\Name('false')), $node->stmts, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Stmt\While_;
    }
}
