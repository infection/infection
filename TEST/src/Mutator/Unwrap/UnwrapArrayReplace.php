<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use function array_keys;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
final class UnwrapArrayReplace extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_replace` function call with each of its operands. For example:

```php
$x = array_replace(['foo', 'bar', 'baz'], ['oof']);
```

Will be mutated to:

```php
$x = ['foo', 'bar', 'baz'];
```

And into:

```php
$x = ['oof'];
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_replace(['foo', 'bar', 'baz'], ['oof']);
# Mutation 1
+ $x = ['foo', 'bar', 'baz'];
# Mutation 2
+ $x = ['oof'];
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_replace';
    }
    /**
    @psalm-mutation-free
    */
    protected function getParameterIndexes(Node\Expr\FuncCall $node) : iterable
    {
        yield from array_keys($node->args);
    }
}
