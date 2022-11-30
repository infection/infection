<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Removal;

use function count;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class MatchArmRemoval implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Removes `match arm`s from `match`.

```php
match ($x) {
    'cond1', 'cond2' => true,
    default => throw new \Exception(),
};
```

Will be mutated to:

```php
match ($x) {
    'cond1' => true,
    default => throw new \Exception(),
};
```

```php
match ($x) {
    'cond2' => true,
    default => throw new \Exception(),
};
```

And:
```php
match ($x) {
    default => throw new \Exception(),
};
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
match ($x) {
-   0 => false,
    1 => true,
    2 => null,
    default => throw new \Exception(),
};
DIFF
);
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\Match_ && count($node->arms) > 1;
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        foreach ($node->arms as $i => $arm) {
            $arms = $node->arms;
            if ($arm->conds !== null && count($arm->conds) > 1) {
                foreach ($arm->conds as $j => $cond) {
                    $conds = $arm->conds;
                    unset($conds[$j]);
                    $arms[$i] = new Node\MatchArm($conds, $arm->body, $node->getAttributes());
                    (yield new Node\Expr\Match_($node->cond, $arms, $node->getAttributes()));
                }
                continue;
            }
            unset($arms[$i]);
            (yield new Node\Expr\Match_($node->cond, $arms, $node->getAttributes()));
        }
    }
}
