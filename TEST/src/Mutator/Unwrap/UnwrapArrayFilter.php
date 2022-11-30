<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapArrayFilter extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_filter` function call with its first operand. For example:

```php
$x = array_filter($array, $callback);
```

Will be mutated to:

```php
$x = $array;
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_filter($array, $callback);
+ $x = $array;
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_filter';
    }
}
