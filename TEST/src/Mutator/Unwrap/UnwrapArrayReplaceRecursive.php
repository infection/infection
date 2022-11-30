<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use function array_keys;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
final class UnwrapArrayReplaceRecursive extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_replace_recursive` function call with its first operand. For example:

```php
$x = array_replace_recursive(['foo', 'bar', 'baz'], ['oof']);
```

Will be mutated to:

```php
$x = ['foo', 'bar', 'baz'];
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_replace_recursive(['foo', 'bar', 'baz'], ['oof']);
+ $x = ['foo', 'bar', 'baz'];
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_replace_recursive';
    }
    /**
    @psalm-mutation-free
    */
    protected function getParameterIndexes(Node\Expr\FuncCall $node) : iterable
    {
        yield from array_keys($node->args);
    }
}
