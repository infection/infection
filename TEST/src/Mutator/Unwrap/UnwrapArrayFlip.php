<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapArrayFlip extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_flip` function call with its first operand. For example:

```php
$x = array_flip($array);
```

Will be mutated to:

```php
$x = $array;
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_flip($array);
+ $x = $array;
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_flip';
    }
}
