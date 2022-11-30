<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapSubstr extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a `substr` function call with its first operand. For example:

```php
$x = substr('abcde', 0, -1);
```

Will be mutated to:

```php
$x = 'abcde';
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = substr('abcde', 0, -1);
+ $x = 'abcde';
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'substr';
    }
}
