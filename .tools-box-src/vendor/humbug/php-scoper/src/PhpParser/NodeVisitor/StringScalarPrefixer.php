<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender\ParentNodeAppender;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\UnexpectedParsingScenario;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Arg;
use _HumbugBoxb47773b41c19\PhpParser\Node\Const_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\Array_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\ArrayItem;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\Assign;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\FuncCall;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\New_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\StaticCall;
use _HumbugBoxb47773b41c19\PhpParser\Node\Identifier;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use _HumbugBoxb47773b41c19\PhpParser\Node\Param;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar\String_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\PropertyProperty;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Return_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
use function array_filter;
use function array_key_exists;
use function array_shift;
use function array_values;
use function explode;
use function implode;
use function in_array;
use function is_string;
use function ltrim;
use function preg_match as native_preg_match;
use function strtolower;
final class StringScalarPrefixer extends NodeVisitorAbstract
{
    private const IGNORED_FUNCTIONS = ['date', 'date_create', 'date_create_from_format', 'gmdate'];
    private const SPECIAL_FUNCTION_NAMES = ['class_alias', 'class_exists', 'define', 'defined', 'function_exists', 'interface_exists', 'is_a', 'is_subclass_of', 'trait_exists'];
    private const DATETIME_CLASSES = ['datetime', 'datetimeimmutable'];
    private const CLASS_LIKE_PATTERN = <<<'REGEX'
/^
    (\\)?               # leading backslash
    (
        [\p{L}_\d]+     # class-like name
        \\              # separator
    )*
    [\p{L}_\d]+         # class-like name
$/ux
REGEX;
    private const CONSTANT_FETCH_PATTERN = <<<'REGEX'
/^
    (\\)?               # leading backslash
    (
        [\p{L}_\d]+     # class-like name
        \\              # separator
    )*
    [\p{L}_\d]+         # class-like name
    ::[\p{L}_\d]+       # constant-like name
$/ux
REGEX;
    public function __construct(private readonly string $prefix, private readonly EnrichedReflector $enrichedReflector)
    {
    }
    public function enterNode(Node $node) : Node
    {
        return $node instanceof String_ ? $this->prefixStringScalar($node) : $node;
    }
    private function prefixStringScalar(String_ $string) : String_
    {
        if (!(ParentNodeAppender::hasParent($string) && is_string($string->value)) || 1 !== native_preg_match(self::CLASS_LIKE_PATTERN, $string->value) && 1 !== native_preg_match(self::CONSTANT_FETCH_PATTERN, $string->value)) {
            return $string;
        }
        $normalizedValue = ltrim($string->value, '\\');
        if ($this->enrichedReflector->belongsToExcludedNamespace($string->value)) {
            return $string;
        }
        $parentNode = ParentNodeAppender::getParent($string);
        if ($parentNode instanceof Arg) {
            return $this->prefixStringArg($string, $parentNode, $normalizedValue);
        }
        if ($parentNode instanceof ArrayItem) {
            return $this->prefixArrayItemString($string, $parentNode, $normalizedValue);
        }
        if (!($parentNode instanceof Assign || $parentNode instanceof Param || $parentNode instanceof Const_ || $parentNode instanceof PropertyProperty || $parentNode instanceof Return_)) {
            return $string;
        }
        return $this->belongsToTheGlobalNamespace($string) ? $string : $this->createPrefixedString($string);
    }
    private function prefixStringArg(String_ $string, Arg $parentNode, string $normalizedValue) : String_
    {
        $callerNode = ParentNodeAppender::getParent($parentNode);
        if ($callerNode instanceof New_) {
            return $this->prefixNewStringArg($string, $callerNode);
        }
        if ($callerNode instanceof FuncCall) {
            return $this->prefixFunctionStringArg($string, $callerNode, $normalizedValue);
        }
        if ($callerNode instanceof StaticCall) {
            return $this->prefixStaticCallStringArg($string, $callerNode);
        }
        return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
    }
    private function prefixNewStringArg(String_ $string, New_ $newNode) : String_
    {
        $class = $newNode->class;
        if (!$class instanceof Name) {
            throw UnexpectedParsingScenario::create();
        }
        if (in_array(strtolower($class->toString()), self::DATETIME_CLASSES, \true)) {
            return $string;
        }
        return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
    }
    private function prefixFunctionStringArg(String_ $string, FuncCall $functionNode, string $normalizedValue) : String_
    {
        $functionName = $functionNode->name instanceof Name ? (string) $functionNode->name : null;
        if (in_array($functionName, self::IGNORED_FUNCTIONS, \true)) {
            return $string;
        }
        if (!in_array($functionName, self::SPECIAL_FUNCTION_NAMES, \true)) {
            return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
        }
        if ('function_exists' === $functionName) {
            return $this->enrichedReflector->isFunctionExcluded($normalizedValue) ? $string : $this->createPrefixedString($string);
        }
        $isConstantNode = self::isConstantNode($string);
        if (!$isConstantNode) {
            if ('define' === $functionName && $this->belongsToTheGlobalNamespace($string)) {
                return $string;
            }
            return $this->enrichedReflector->isClassExcluded($normalizedValue) ? $string : $this->createPrefixedString($string);
        }
        return $this->enrichedReflector->isExposedConstant($normalizedValue) ? $string : $this->createPrefixedString($string);
    }
    private function prefixStaticCallStringArg(String_ $string, StaticCall $callNode) : String_
    {
        $class = $callNode->class;
        if (!$class instanceof Name) {
            return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
        }
        if (!in_array(strtolower($class->toString()), self::DATETIME_CLASSES, \true)) {
            return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
        }
        if ($callNode->name instanceof Identifier && 'createFromFormat' === $callNode->name->toString()) {
            return $string;
        }
        return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
    }
    private function prefixArrayItemString(String_ $string, ArrayItem $parentNode, string $normalizedValue) : String_
    {
        $arrayItemNode = $parentNode;
        $parentNode = ParentNodeAppender::getParent($parentNode);
        if (!$parentNode instanceof Array_) {
            return $string;
        }
        $arrayNode = $parentNode;
        $parentNode = ParentNodeAppender::getParent($parentNode);
        if (!$parentNode instanceof Arg || !ParentNodeAppender::hasParent($parentNode)) {
            return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
        }
        $functionNode = ParentNodeAppender::getParent($parentNode);
        if (!$functionNode instanceof FuncCall) {
            return $this->createPrefixedStringIfDoesNotBelongToGlobalNamespace($string);
        }
        if (!$functionNode->name instanceof Name) {
            return $string;
        }
        $functionName = (string) $functionNode->name;
        return 'spl_autoload_register' === $functionName && array_key_exists(0, $arrayNode->items) && $arrayItemNode === $arrayNode->items[0] && !$this->enrichedReflector->isClassExcluded($normalizedValue) ? $this->createPrefixedString($string) : $string;
    }
    private static function isConstantNode(String_ $node) : bool
    {
        $parent = ParentNodeAppender::getParent($node);
        if (!$parent instanceof Arg) {
            throw UnexpectedParsingScenario::create();
        }
        $argParent = ParentNodeAppender::getParent($parent);
        if (!$argParent instanceof FuncCall) {
            throw UnexpectedParsingScenario::create();
        }
        if (!$argParent->name instanceof Name || !in_array((string) $argParent->name, ['define', 'defined'], \true)) {
            return \false;
        }
        return $parent === $argParent->args[0];
    }
    private function createPrefixedStringIfDoesNotBelongToGlobalNamespace(String_ $string) : String_
    {
        return $this->belongsToTheGlobalNamespace($string) ? $string : $this->createPrefixedString($string);
    }
    private function belongsToTheGlobalNamespace(String_ $string) : bool
    {
        return $this->enrichedReflector->belongsToGlobalNamespace($string->value);
    }
    private function createPrefixedString(String_ $previous) : String_
    {
        $previousValueParts = array_values(array_filter(explode('\\', $previous->value)));
        $previousValueAlreadyPrefixed = $this->prefix === $previousValueParts[0];
        if ($previousValueAlreadyPrefixed) {
            array_shift($previousValueParts);
        }
        $previousValue = implode('\\', $previousValueParts);
        $string = new String_((string) FullyQualified::concat($this->prefix, $previousValue), $previous->getAttributes());
        ParentNodeAppender::setParent($string, $string);
        return $string;
    }
}
