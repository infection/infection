<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapArrayUnique extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_unique` function call with its array operand. For example:

```php
$x = array_unique(['a', 'a', 'b']);
```

Will be mutated to:

```php
$x = ['a', 'a', 'b'];
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_unique(['a', 'a', 'b']);
+ $x = ['a', 'a', 'b'];
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_unique';
    }
}
