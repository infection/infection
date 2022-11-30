<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Extensions;

use function array_fill_keys;
use function array_intersect_key;
use function array_key_exists;
use function array_slice;
use Closure;
use function constant;
use function count;
use function defined;
use Generator;
use _HumbugBox9658796bb9f0\Infection\Mutator\ConfigurableMutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetConfigClassName;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class MBString implements ConfigurableMutator
{
    use GetConfigClassName;
    use GetMutatorName;
    private array $converters;
    public function __construct(MBStringConfig $config)
    {
        $this->converters = self::createConverters($config->getAllowedFunctions());
    }
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a statement making use of the mbstring extension with its vanilla code equivalent. For
example:

```php
$x = mb_strlen($str) < 10;
```

Will be mutated to:

```php
$x = strlen($str) < 10;
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = mb_strlen($str) < 10;
+ $x = strlen($str) < 10;
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
        return array_key_exists($node->name->toLowerString(), $this->converters);
    }
    private static function createConverters(array $allowedFunctions) : array
    {
        return array_intersect_key(['mb_chr' => self::makeFunctionAndRemoveExtraArgsMapper('chr', 1), 'mb_ord' => self::makeFunctionAndRemoveExtraArgsMapper('ord', 1), 'mb_parse_str' => self::makeFunctionMapper('parse_str'), 'mb_send_mail' => self::makeFunctionMapper('mail'), 'mb_strcut' => self::makeFunctionAndRemoveExtraArgsMapper('substr', 3), 'mb_stripos' => self::makeFunctionAndRemoveExtraArgsMapper('stripos', 3), 'mb_stristr' => self::makeFunctionAndRemoveExtraArgsMapper('stristr', 3), 'mb_strlen' => self::makeFunctionAndRemoveExtraArgsMapper('strlen', 1), 'mb_strpos' => self::makeFunctionAndRemoveExtraArgsMapper('strpos', 3), 'mb_strrchr' => self::makeFunctionAndRemoveExtraArgsMapper('strrchr', 2), 'mb_strripos' => self::makeFunctionAndRemoveExtraArgsMapper('strripos', 3), 'mb_strrpos' => self::makeFunctionAndRemoveExtraArgsMapper('strrpos', 3), 'mb_strstr' => self::makeFunctionAndRemoveExtraArgsMapper('strstr', 3), 'mb_strtolower' => self::makeFunctionAndRemoveExtraArgsMapper('strtolower', 1), 'mb_strtoupper' => self::makeFunctionAndRemoveExtraArgsMapper('strtoupper', 1), 'mb_str_split' => self::makeFunctionAndRemoveExtraArgsMapper('str_split', 2), 'mb_substr_count' => self::makeFunctionAndRemoveExtraArgsMapper('substr_count', 2), 'mb_substr' => self::makeFunctionAndRemoveExtraArgsMapper('substr', 3), 'mb_convert_case' => self::makeConvertCaseMapper()], array_fill_keys($allowedFunctions, null));
    }
    private static function makeFunctionMapper(string $newFunctionName) : Closure
    {
        return static function (Node\Expr\FuncCall $node) use($newFunctionName) : iterable {
            (yield self::mapFunctionCall($node, $newFunctionName, $node->args));
        };
    }
    private static function makeFunctionAndRemoveExtraArgsMapper(string $newFunctionName, int $argsAtMost) : Closure
    {
        return static function (Node\Expr\FuncCall $node) use($newFunctionName, $argsAtMost) : iterable {
            (yield self::mapFunctionCall($node, $newFunctionName, array_slice($node->args, 0, $argsAtMost)));
        };
    }
    private static function makeConvertCaseMapper() : Closure
    {
        return static function (Node\Expr\FuncCall $node) : Generator {
            $modeValue = self::getConvertCaseModeValue($node);
            if ($modeValue === null) {
                return;
            }
            $functionName = self::getConvertCaseFunctionName($modeValue);
            if ($functionName === null) {
                return;
            }
            (yield self::mapFunctionCall($node, $functionName, [$node->args[0]]));
        };
    }
    private static function getConvertCaseModeValue(Node\Expr\FuncCall $node) : ?int
    {
        if (count($node->args) < 2) {
            return null;
        }
        if ($node->args[1] instanceof Node\VariadicPlaceholder) {
            return null;
        }
        $mode = $node->args[1]->value;
        if ($mode instanceof Node\Scalar\LNumber) {
            return $mode->value;
        }
        if ($mode instanceof Node\Expr\ConstFetch) {
            return constant($mode->name->toString());
        }
        return null;
    }
    private static function getConvertCaseFunctionName(int $mode) : ?string
    {
        if (self::isInMbCaseMode($mode, 'MB_CASE_UPPER', 'MB_CASE_UPPER_SIMPLE')) {
            return 'strtoupper';
        }
        if (self::isInMbCaseMode($mode, 'MB_CASE_LOWER', 'MB_CASE_LOWER_SIMPLE', 'MB_CASE_FOLD', 'MB_CASE_FOLD_SIMPLE')) {
            return 'strtolower';
        }
        if (self::isInMbCaseMode($mode, 'MB_CASE_TITLE', 'MB_CASE_TITLE_SIMPLE')) {
            return 'ucwords';
        }
        return null;
    }
    private static function isInMbCaseMode(int $mode, string ...$cases) : bool
    {
        foreach ($cases as $constant) {
            if (defined($constant) && constant($constant) === $mode) {
                return \true;
            }
        }
        return \false;
    }
    private static function mapFunctionCall(Node\Expr\FuncCall $node, string $newFuncName, array $args) : Node\Expr\FuncCall
    {
        return new Node\Expr\FuncCall(new Node\Name($newFuncName, $node->name->getAttributes()), $args, $node->getAttributes());
    }
}
