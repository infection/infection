<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Regex;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class PregQuote implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Removes a `preg_quote` function call with its operand. For example:

```php
$x = preg_quote($string, $delimiter);
```

Will be mutated to:

```php
$x = $string;
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = preg_quote($string, $delimiter);
+ $x = $string;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        if ($node->args[0] instanceof Node\VariadicPlaceholder) {
            return [];
        }
        (yield $node->args[0]);
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name && $node->name->toLowerString() === 'preg_quote';
    }
}
