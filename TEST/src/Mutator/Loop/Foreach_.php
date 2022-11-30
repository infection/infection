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
final class Foreach_ implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces the iterable being iterated over with a `foreach` statement with an empty array, preventing
any statement within the block to be executed. For example:

```php`
foreach ($a as $b) {
    // ...
}
```

Will be mutated to:

```php
for ([] as $b]) {
    // ...
}
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- foreach ($a as $b) {
+ for ([] as $b]) {
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
        (yield new Node\Stmt\Foreach_(new Node\Expr\Array_(), $node->valueVar, ['keyVar' => $node->keyVar, 'byRef' => $node->byRef, 'stmts' => $node->stmts], $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Stmt\Foreach_;
    }
}
