<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapUcFirst extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a `ucfirst` function call with its first operand. For example:

```php
$x = ucfirst('Hello!');
```

Will be mutated to:

```php
$x = 'Hello!';
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = ucfirst('Hello!');
+ $x = 'Hello!';
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'ucfirst';
    }
}
