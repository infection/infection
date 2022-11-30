<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Regex;

use function count;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class PregMatchMatches implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a `preg_match` search results with an empty result. For example:

```php
if (preg_match('/pattern/', $subject, $matches, $flags)) {
    // ...
}
```

Will be mutated to:

```php
if ((int) $matches = []) {
    // ...
}
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- preg_match('/pattern/', $subject, $matches, $flags);
+ (int) $matches = [];
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        if ($node->args[2] instanceof Node\VariadicPlaceholder) {
            return [];
        }
        (yield new Node\Expr\Cast\Int_(new Node\Expr\Assign($node->args[2]->value, new Node\Expr\Array_())));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\FuncCall) {
            return \false;
        }
        if (!$node->name instanceof Node\Name || $node->name->toLowerString() !== 'preg_match') {
            return \false;
        }
        return count($node->args) >= 3;
    }
}
