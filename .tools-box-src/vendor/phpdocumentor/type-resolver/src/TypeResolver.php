<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection;

use ArrayIterator;
use InvalidArgumentException;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\CallableString;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\False_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\HtmlEscapedString;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\IntegerRange;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\List_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\LiteralString;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\LowercaseString;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\NegativeInteger;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\NonEmptyLowercaseString;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\NonEmptyString;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\Numeric_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\NumericString;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\PositiveInteger;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\TraitString;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes\True_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Array_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\ArrayKey;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Boolean;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Callable_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\ClassString;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Collection;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Compound;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Expression;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Float_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Integer;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\InterfaceString;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Intersection;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Iterable_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Mixed_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Never_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Null_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Nullable;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Object_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Parent_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Resource_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Scalar;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Self_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Static_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\String_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\This;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Void_;
use RuntimeException;
use function array_key_exists;
use function array_key_last;
use function array_pop;
use function array_values;
use function class_exists;
use function class_implements;
use function count;
use function current;
use function in_array;
use function is_numeric;
use function preg_split;
use function strpos;
use function strtolower;
use function trim;
use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;
final class TypeResolver
{
    private const OPERATOR_ARRAY = '[]';
    private const OPERATOR_NAMESPACE = '\\';
    private const PARSER_IN_COMPOUND = 0;
    private const PARSER_IN_NULLABLE = 1;
    private const PARSER_IN_ARRAY_EXPRESSION = 2;
    private const PARSER_IN_COLLECTION_EXPRESSION = 3;
    /**
    @psalm-var
    */
    private array $keywords = ['string' => String_::class, 'class-string' => ClassString::class, 'interface-string' => InterfaceString::class, 'html-escaped-string' => HtmlEscapedString::class, 'lowercase-string' => LowercaseString::class, 'non-empty-lowercase-string' => NonEmptyLowercaseString::class, 'non-empty-string' => NonEmptyString::class, 'numeric-string' => NumericString::class, 'numeric' => Numeric_::class, 'trait-string' => TraitString::class, 'int' => Integer::class, 'integer' => Integer::class, 'positive-int' => PositiveInteger::class, 'negative-int' => NegativeInteger::class, 'bool' => Boolean::class, 'boolean' => Boolean::class, 'real' => Float_::class, 'float' => Float_::class, 'double' => Float_::class, 'object' => Object_::class, 'mixed' => Mixed_::class, 'array' => Array_::class, 'array-key' => ArrayKey::class, 'resource' => Resource_::class, 'void' => Void_::class, 'null' => Null_::class, 'scalar' => Scalar::class, 'callback' => Callable_::class, 'callable' => Callable_::class, 'callable-string' => CallableString::class, 'false' => False_::class, 'true' => True_::class, 'literal-string' => LiteralString::class, 'self' => Self_::class, '$this' => This::class, 'static' => Static_::class, 'parent' => Parent_::class, 'iterable' => Iterable_::class, 'never' => Never_::class, 'list' => List_::class];
    /**
    @psalm-readonly */
    private FqsenResolver $fqsenResolver;
    public function __construct(?FqsenResolver $fqsenResolver = null)
    {
        $this->fqsenResolver = $fqsenResolver ?: new FqsenResolver();
    }
    public function resolve(string $type, ?Context $context = null) : Type
    {
        $type = trim($type);
        if (!$type) {
            throw new InvalidArgumentException('Attempted to resolve "' . $type . '" but it appears to be empty');
        }
        if ($context === null) {
            $context = new Context('');
        }
        $tokens = preg_split('/(\\||\\?|<|>|&|, ?|\\(|\\)|\\[\\]+)/', $type, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if ($tokens === \false) {
            throw new InvalidArgumentException('Unable to split the type string "' . $type . '" into tokens');
        }
        $tokenIterator = new ArrayIterator($tokens);
        return $this->parseTypes($tokenIterator, $context, self::PARSER_IN_COMPOUND);
    }
    private function parseTypes(ArrayIterator $tokens, Context $context, int $parserContext) : Type
    {
        $types = [];
        $token = '';
        $compoundToken = '|';
        while ($tokens->valid()) {
            $token = $tokens->current();
            if ($token === null) {
                throw new RuntimeException('Unexpected nullable character');
            }
            if ($token === '|' || $token === '&') {
                if (count($types) === 0) {
                    throw new RuntimeException('A type is missing before a type separator');
                }
                if (!in_array($parserContext, [self::PARSER_IN_COMPOUND, self::PARSER_IN_ARRAY_EXPRESSION, self::PARSER_IN_COLLECTION_EXPRESSION, self::PARSER_IN_NULLABLE], \true)) {
                    throw new RuntimeException('Unexpected type separator');
                }
                $compoundToken = $token;
                $tokens->next();
            } elseif ($token === '?') {
                if (!in_array($parserContext, [self::PARSER_IN_COMPOUND, self::PARSER_IN_ARRAY_EXPRESSION, self::PARSER_IN_COLLECTION_EXPRESSION, self::PARSER_IN_NULLABLE], \true)) {
                    throw new RuntimeException('Unexpected nullable character');
                }
                $tokens->next();
                $type = $this->parseTypes($tokens, $context, self::PARSER_IN_NULLABLE);
                $types[] = new Nullable($type);
            } elseif ($token === '(') {
                $tokens->next();
                $type = $this->parseTypes($tokens, $context, self::PARSER_IN_ARRAY_EXPRESSION);
                $token = $tokens->current();
                if ($token === null) {
                    break;
                }
                $tokens->next();
                $resolvedType = new Expression($type);
                $types[] = $resolvedType;
            } elseif ($parserContext === self::PARSER_IN_ARRAY_EXPRESSION && isset($token[0]) && $token[0] === ')') {
                break;
            } elseif ($token === '<') {
                if (count($types) === 0) {
                    throw new RuntimeException('Unexpected collection operator "<", class name is missing');
                }
                $classType = array_pop($types);
                if ($classType !== null) {
                    if ((string) $classType === 'class-string') {
                        $types[] = $this->resolveClassString($tokens, $context);
                    } elseif ((string) $classType === 'int') {
                        $types[] = $this->resolveIntRange($tokens);
                    } elseif ((string) $classType === 'interface-string') {
                        $types[] = $this->resolveInterfaceString($tokens, $context);
                    } else {
                        $types[] = $this->resolveCollection($tokens, $classType, $context);
                    }
                }
                $tokens->next();
            } elseif ($parserContext === self::PARSER_IN_COLLECTION_EXPRESSION && ($token === '>' || trim($token) === ',')) {
                break;
            } elseif ($token === self::OPERATOR_ARRAY) {
                $last = array_key_last($types);
                if ($last === null) {
                    throw new InvalidArgumentException('Unexpected array operator');
                }
                $lastItem = $types[$last];
                if ($lastItem instanceof Expression) {
                    $lastItem = $lastItem->getValueType();
                }
                $types[$last] = new Array_($lastItem);
                $tokens->next();
            } else {
                $types[] = $this->resolveSingleType($token, $context);
                $tokens->next();
            }
        }
        if ($token === '|' || $token === '&') {
            throw new RuntimeException('A type is missing after a type separator');
        }
        if (count($types) === 0) {
            if ($parserContext === self::PARSER_IN_NULLABLE) {
                throw new RuntimeException('A type is missing after a nullable character');
            }
            if ($parserContext === self::PARSER_IN_ARRAY_EXPRESSION) {
                throw new RuntimeException('A type is missing in an array expression');
            }
            if ($parserContext === self::PARSER_IN_COLLECTION_EXPRESSION) {
                throw new RuntimeException('A type is missing in a collection expression');
            }
        } elseif (count($types) === 1) {
            return current($types);
        }
        if ($compoundToken === '|') {
            return new Compound(array_values($types));
        }
        return new Intersection(array_values($types));
    }
    /**
    @psalm-mutation-free
    */
    private function resolveSingleType(string $type, Context $context) : object
    {
        switch (\true) {
            case $this->isKeyword($type):
                return $this->resolveKeyword($type);
            case $this->isFqsen($type):
                return $this->resolveTypedObject($type);
            case $this->isPartialStructuralElementName($type):
                return $this->resolveTypedObject($type, $context);
            default:
                throw new RuntimeException('Unable to resolve type "' . $type . '", there is no known method to resolve it');
        }
    }
    /**
    @psalm-param
    */
    public function addKeyword(string $keyword, string $typeClassName) : void
    {
        if (!class_exists($typeClassName)) {
            throw new InvalidArgumentException('The Value Object that needs to be created with a keyword "' . $keyword . '" must be an existing class' . ' but we could not find the class ' . $typeClassName);
        }
        $interfaces = class_implements($typeClassName);
        if ($interfaces === \false) {
            throw new InvalidArgumentException('The Value Object that needs to be created with a keyword "' . $keyword . '" must be an existing class' . ' but we could not find the class ' . $typeClassName);
        }
        if (!in_array(Type::class, $interfaces, \true)) {
            throw new InvalidArgumentException('The class "' . $typeClassName . '" must implement the interface "phpDocumentor\\Reflection\\Type"');
        }
        $this->keywords[$keyword] = $typeClassName;
    }
    /**
    @psalm-mutation-free
    */
    private function isKeyword(string $type) : bool
    {
        return array_key_exists(strtolower($type), $this->keywords);
    }
    /**
    @psalm-mutation-free
    */
    private function isPartialStructuralElementName(string $type) : bool
    {
        return isset($type[0]) && $type[0] !== self::OPERATOR_NAMESPACE && !$this->isKeyword($type);
    }
    /**
    @psalm-mutation-free
    */
    private function isFqsen(string $type) : bool
    {
        return strpos($type, self::OPERATOR_NAMESPACE) === 0;
    }
    /**
    @psalm-mutation-free
    */
    private function resolveKeyword(string $type) : Type
    {
        $className = $this->keywords[strtolower($type)];
        return new $className();
    }
    /**
    @psalm-mutation-free
    */
    private function resolveTypedObject(string $type, ?Context $context = null) : Object_
    {
        return new Object_($this->fqsenResolver->resolve($type, $context));
    }
    private function resolveClassString(ArrayIterator $tokens, Context $context) : Type
    {
        $tokens->next();
        $classType = $this->parseTypes($tokens, $context, self::PARSER_IN_COLLECTION_EXPRESSION);
        if (!$classType instanceof Object_ || $classType->getFqsen() === null) {
            throw new RuntimeException($classType . ' is not a class string');
        }
        $token = $tokens->current();
        if ($token !== '>') {
            if (empty($token)) {
                throw new RuntimeException('class-string: ">" is missing');
            }
            throw new RuntimeException('Unexpected character "' . $token . '", ">" is missing');
        }
        return new ClassString($classType->getFqsen());
    }
    private function resolveIntRange(ArrayIterator $tokens) : Type
    {
        $tokens->next();
        $token = '';
        $minValue = null;
        $maxValue = null;
        $commaFound = \false;
        $tokenCounter = 0;
        while ($tokens->valid()) {
            $tokenCounter++;
            $token = $tokens->current();
            if ($token === null) {
                throw new RuntimeException('Unexpected nullable character');
            }
            $token = trim($token);
            if ($token === '>') {
                break;
            }
            if ($token === ',') {
                $commaFound = \true;
            }
            if ($commaFound === \false && $minValue === null) {
                if (is_numeric($token) || $token === 'max' || $token === 'min') {
                    $minValue = $token;
                }
            }
            if ($commaFound === \true && $maxValue === null) {
                if (is_numeric($token) || $token === 'max' || $token === 'min') {
                    $maxValue = $token;
                }
            }
            $tokens->next();
        }
        if ($token !== '>') {
            if (empty($token)) {
                throw new RuntimeException('interface-string: ">" is missing');
            }
            throw new RuntimeException('Unexpected character "' . $token . '", ">" is missing');
        }
        if ($minValue === null || $maxValue === null || $tokenCounter > 4) {
            throw new RuntimeException('int<min,max> has not the correct format');
        }
        return new IntegerRange($minValue, $maxValue);
    }
    private function resolveInterfaceString(ArrayIterator $tokens, Context $context) : Type
    {
        $tokens->next();
        $classType = $this->parseTypes($tokens, $context, self::PARSER_IN_COLLECTION_EXPRESSION);
        if (!$classType instanceof Object_ || $classType->getFqsen() === null) {
            throw new RuntimeException($classType . ' is not a interface string');
        }
        $token = $tokens->current();
        if ($token !== '>') {
            if (empty($token)) {
                throw new RuntimeException('interface-string: ">" is missing');
            }
            throw new RuntimeException('Unexpected character "' . $token . '", ">" is missing');
        }
        return new InterfaceString($classType->getFqsen());
    }
    private function resolveCollection(ArrayIterator $tokens, Type $classType, Context $context) : Type
    {
        $isArray = (string) $classType === 'array';
        $isIterable = (string) $classType === 'iterable';
        $isList = (string) $classType === 'list';
        if (!$isArray && !$isIterable && !$isList && (!$classType instanceof Object_ || $classType->getFqsen() === null)) {
            throw new RuntimeException($classType . ' is not a collection');
        }
        $tokens->next();
        $valueType = $this->parseTypes($tokens, $context, self::PARSER_IN_COLLECTION_EXPRESSION);
        $keyType = null;
        $token = $tokens->current();
        if ($token !== null && trim($token) === ',' && !$isList) {
            $keyType = $valueType;
            if ($isArray) {
                if (!$keyType instanceof ArrayKey && !$keyType instanceof String_ && !$keyType instanceof Integer && !$keyType instanceof Compound) {
                    throw new RuntimeException('An array can have only integers or strings as keys');
                }
                if ($keyType instanceof Compound) {
                    foreach ($keyType->getIterator() as $item) {
                        if (!$item instanceof ArrayKey && !$item instanceof String_ && !$item instanceof Integer) {
                            throw new RuntimeException('An array can have only integers or strings as keys');
                        }
                    }
                }
            }
            $tokens->next();
            $valueType = $this->parseTypes($tokens, $context, self::PARSER_IN_COLLECTION_EXPRESSION);
        }
        $token = $tokens->current();
        if ($token !== '>') {
            if (empty($token)) {
                throw new RuntimeException('Collection: ">" is missing');
            }
            throw new RuntimeException('Unexpected character "' . $token . '", ">" is missing');
        }
        if ($isArray) {
            return new Array_($valueType, $keyType);
        }
        if ($isIterable) {
            return new Iterable_($valueType, $keyType);
        }
        if ($isList) {
            return new List_($valueType);
        }
        if ($classType instanceof Object_) {
            return $this->makeCollectionFromObject($classType, $valueType, $keyType);
        }
        throw new RuntimeException('Invalid $classType provided');
    }
    /**
    @psalm-pure
    */
    private function makeCollectionFromObject(Object_ $object, Type $valueType, ?Type $keyType = null) : Collection
    {
        return new Collection($object->getFqsen(), $valueType, $keyType);
    }
}
