<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapLtrim extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a `ltrim` function call with its first operand. For example:

```php
$x = ltrim(' Hello!');
```

Will be mutated to:

```php
$x = ' Hello!';
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = ltrim(' Hello!');
+ $x = ' Hello!';
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'ltrim';
    }
}
