<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapArrayValues extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a `array_values` function call with its array operand. For example:

```php
$x = array_values([10 => 'Hello!']);
```

Will be mutated to:

```php
$x = [10 => 'Hello!'];
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_values([10 => 'Hello!']);
+ $x = [10 => 'Hello!'];
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_values';
    }
}
