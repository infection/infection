<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Removal;

use function count;
use _HumbugBox9658796bb9f0\Infection\Mutator\ConfigurableMutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetConfigClassName;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ParentConnector;
use function min;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\Node\Expr\ArrayItem;
use function range;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
/**
@implements
*/
final class ArrayItemRemoval implements ConfigurableMutator
{
    use GetConfigClassName;
    use GetMutatorName;
    public function __construct(private ArrayItemRemovalConfig $config)
    {
    }
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Removes an element of an array literal. For example:

```php
$x = [0, 1, 2];
```

Will be mutated to:

```php
$x = [1, 2];
```

And:

```php
$x = [0, 2];
```

And:

```php
$x = [0, 1];
```

Which elements it removes or how many elements it will attempt to remove will depend on its
configuration.

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = [0, 1, 2];
# Mutation 1
+ $x = [1, 2];
# Mutation 2
+ $x = [0, 2];
# Mutation 3
+ $x = [0, 1];
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        Assert::allNotNull($node->items);
        foreach ($this->getItemsIndexes($node->items) as $indexToRemove) {
            $newArrayNode = clone $node;
            unset($newArrayNode->items[$indexToRemove]);
            (yield $newArrayNode);
        }
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\Array_) {
            return \false;
        }
        if ($node->items === []) {
            return \false;
        }
        $parent = ParentConnector::findParent($node);
        if ($parent instanceof Node\Expr\Assign && $parent->var === $node) {
            return \false;
        }
        return \true;
    }
    /**
    @psalm-mutation-free
    */
    private function getItemsIndexes(array $items) : array
    {
        return match ($this->config->getRemove()) {
            'first' => [0],
            'last' => [count($items) - 1],
            default => range(0, min(count($items), $this->config->getLimit()) - 1),
        };
    }
}
