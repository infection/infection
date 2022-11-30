<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
final class UnwrapStrIreplace extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a `str_ireplace` function call with its third operand. For example:

```php
$x = str_ireplace('%body%', 'black', '<body text=%BODY%>');
```

Will be mutated to:

```php
$x = '<body text=%BODY%>';
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = str_ireplace('%body%', 'black', '<body text=%BODY%>');
+ $x = '<body text=%BODY%>';
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'str_ireplace';
    }
    /**
    @psalm-pure
    */
    protected function getParameterIndexes(Node\Expr\FuncCall $node) : iterable
    {
        (yield 2);
    }
}
