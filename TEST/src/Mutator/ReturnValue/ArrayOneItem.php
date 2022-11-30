<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\ReturnValue;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ReflectionVisitor;
use function is_string;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class ArrayOneItem implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Leaves only one item in the returned array. For example:

```php
return $array;
```

Will be mutated to:

```php
return count($array) > 1 ?
    array_slice($array, 0, 1, true) :
    $array
;
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- return $array;
+ return count($array) > 1 ? array_slice($array, 0, 1, true) : $array;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        $expression = $node->expr;
        $arrayVariable = new Node\Expr\Variable($expression->name);
        (yield new Node\Stmt\Return_(new Node\Expr\Ternary(new Node\Expr\BinaryOp\Greater(new Node\Expr\FuncCall(new Node\Name('count'), [new Node\Arg($arrayVariable)]), new Node\Scalar\LNumber(1)), new Node\Expr\FuncCall(new Node\Name('array_slice'), [new Node\Arg($arrayVariable), new Node\Arg(new Node\Scalar\LNumber(0)), new Node\Arg(new Node\Scalar\LNumber(1)), new Node\Arg(new Node\Expr\ConstFetch(new Node\Name('true')))]), $arrayVariable)));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return \false;
        }
        if (!$node->expr instanceof Node\Expr\Variable) {
            return \false;
        }
        return $this->returnTypeIsArray($node);
    }
    private function returnTypeIsArray(Node $node) : bool
    {
        $functionScope = $node->getAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY, null);
        if ($functionScope === null) {
            return \false;
        }
        $returnType = $functionScope->getReturnType();
        if ($returnType instanceof Node\Identifier) {
            $returnType = $returnType->name;
        }
        return is_string($returnType) && $returnType === 'array';
    }
}
