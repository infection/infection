<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Extensions;

use function array_fill_keys;
use function array_intersect_key;
use Closure;
use function count;
use _HumbugBox9658796bb9f0\Infection\Mutator\ConfigurableMutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetConfigClassName;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class BCMath implements ConfigurableMutator
{
    use GetConfigClassName;
    use GetMutatorName;
    private array $converters;
    public function __construct(BCMathConfig $config)
    {
        $this->converters = self::createConverters($config->getAllowedFunctions());
    }
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a statement making use of the bcmath extension with its vanilla code equivalent. For example:

```php`
$x = bcadd($a, $b);
```

Will be mutated to:

```php
$x = (string) ($a + $b);
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = bcadd($a, $b);
+ $x = (string) ($a + $b);
DIFF
);
    }
    /**
    @psalm-mutation-free
    @psalm-suppress
    */
    public function mutate(Node $node) : iterable
    {
        $name = $node->name;
        yield from $this->converters[$name->toLowerString()]($node);
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\FuncCall || !$node->name instanceof Node\Name) {
            return \false;
        }
        return isset($this->converters[$node->name->toLowerString()]);
    }
    private static function createConverters(array $functionsMap) : array
    {
        return array_intersect_key(['bcadd' => self::makeCheckingMinArgsMapper(2, self::makeCastToStringMapper(self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Plus::class))), 'bcdiv' => self::makeCheckingMinArgsMapper(2, self::makeCastToStringMapper(self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Div::class))), 'bcmod' => self::makeCheckingMinArgsMapper(2, self::makeCastToStringMapper(self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Mod::class))), 'bcmul' => self::makeCheckingMinArgsMapper(2, self::makeCastToStringMapper(self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Mul::class))), 'bcpow' => self::makeCheckingMinArgsMapper(2, self::makeCastToStringMapper(self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Pow::class))), 'bcsub' => self::makeCheckingMinArgsMapper(2, self::makeCastToStringMapper(self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Minus::class))), 'bcsqrt' => self::makeCheckingMinArgsMapper(1, self::makeCastToStringMapper(self::makeSquareRootsMapper())), 'bcpowmod' => self::makeCheckingMinArgsMapper(3, self::makeCastToStringMapper(self::makePowerModuloMapper())), 'bccomp' => self::makeCheckingMinArgsMapper(2, self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Spaceship::class))], array_fill_keys($functionsMap, null));
    }
    private static function makeCheckingMinArgsMapper(int $minimumArgsCount, Closure $converter) : Closure
    {
        return static function (Node\Expr\FuncCall $node) use($minimumArgsCount, $converter) : iterable {
            if (count($node->args) >= $minimumArgsCount) {
                yield from $converter($node);
            }
        };
    }
    private static function makeCastToStringMapper(Closure $converter) : Closure
    {
        return static function (Node\Expr\FuncCall $node) use($converter) : iterable {
            foreach ($converter($node) as $newNode) {
                (yield new Node\Expr\Cast\String_($newNode));
            }
        };
    }
    /**
    @phpstan-param
    */
    private static function makeBinaryOperatorMapper(string $operator) : Closure
    {
        return static function (Node\Expr\FuncCall $node) use($operator) : iterable {
            if ($node->args[0] instanceof Node\VariadicPlaceholder || $node->args[1] instanceof Node\VariadicPlaceholder) {
                return;
            }
            (yield new $operator($node->args[0]->value, $node->args[1]->value));
        };
    }
    private static function makeSquareRootsMapper() : Closure
    {
        return static function (Node\Expr\FuncCall $node) : iterable {
            (yield new Node\Expr\FuncCall(new Node\Name('\\sqrt'), [$node->args[0]]));
        };
    }
    private static function makePowerModuloMapper() : Closure
    {
        return static function (Node\Expr\FuncCall $node) : iterable {
            if ($node->args[2] instanceof Node\VariadicPlaceholder) {
                return;
            }
            (yield new Node\Expr\BinaryOp\Mod(new Node\Expr\FuncCall(new Node\Name('\\pow'), [$node->args[0], $node->args[1]]), $node->args[2]->value));
        };
    }
}
