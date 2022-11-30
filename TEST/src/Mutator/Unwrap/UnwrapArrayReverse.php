<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapArrayReverse extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_reverse` function call with its first operand. For example:

```php
$x = array_reverse(['foo', 'bar', 'baz'], 1);
```

Will be mutated to:

```php
$x = ['foo', 'bar', 'baz'];
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_reverse(['foo', 'bar', 'baz'], 1);
+ $x = ['foo', 'bar', 'baz'];
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_reverse';
    }
}
