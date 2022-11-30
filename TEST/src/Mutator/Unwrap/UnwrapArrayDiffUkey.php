<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapArrayDiffUkey extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_diff_ukey` function call with its first operand. For example:

```php
$x = array_diff_ukey($array1, $array2, $keyCompareFunc);
```

Will be mutated to:

```php
$x = $array1;
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_diff_ukey($array1, $array2, $keyCompareFunc);
+ $x = $array1;
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_diff_ukey';
    }
}
