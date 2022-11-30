<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
final class UnwrapStrReplace extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a `str_replace` function call with its third operand. For example:

```php
$x = str_replace('%body%', 'black', '<body text=%body%>');
```

Will be mutated to:

```php
$x = '<body text=%body%>';
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = str_replace('%body%', 'black', '<body text=%body%>');
+ $x = '<body text=%body%>';
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'str_replace';
    }
    /**
    @psalm-pure
    */
    protected function getParameterIndexes(Node\Expr\FuncCall $node) : iterable
    {
        (yield 2);
    }
}
