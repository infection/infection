<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapArrayKeys extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_keys` function call with its operand. For example:

```php
$x = array_keys(['foo' => 'bar']);
```

Will be mutated to:

```php
$x = ['foo' => 'bar'];
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_keys(['foo' => 'bar']);
+ $x = ['foo' => 'bar'];
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_keys';
    }
}
