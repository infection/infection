<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Operator;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class Throw_ implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Removes a throw statement (`throw`). For example:

```php
throw new Exception();
```

Will be mutated to:

```php
new Exception();
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- throw new Exception();
+ new Exception();
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Stmt\Expression($node->expr));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Stmt\Throw_;
    }
}
