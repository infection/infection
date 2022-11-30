<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser;

use _HumbugBox9658796bb9f0\PhpParser\Node\ComplexType;
use _HumbugBox9658796bb9f0\PhpParser\Node\Expr;
use _HumbugBox9658796bb9f0\PhpParser\Node\Identifier;
use _HumbugBox9658796bb9f0\PhpParser\Node\Name;
use _HumbugBox9658796bb9f0\PhpParser\Node\NullableType;
use _HumbugBox9658796bb9f0\PhpParser\Node\Scalar;
use _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;
final class BuilderHelpers
{
    public static function normalizeNode($node) : Node
    {
        if ($node instanceof Builder) {
            return $node->getNode();
        }
        if ($node instanceof Node) {
            return $node;
        }
        throw new \LogicException('Expected node or builder object');
    }
    public static function normalizeStmt($node) : Stmt
    {
        $node = self::normalizeNode($node);
        if ($node instanceof Stmt) {
            return $node;
        }
        if ($node instanceof Expr) {
            return new Stmt\Expression($node);
        }
        throw new \LogicException('Expected statement or expression node');
    }
    public static function normalizeIdentifier($name) : Identifier
    {
        if ($name instanceof Identifier) {
            return $name;
        }
        if (\is_string($name)) {
            return new Identifier($name);
        }
        throw new \LogicException('Expected string or instance of Node\\Identifier');
    }
    public static function normalizeIdentifierOrExpr($name)
    {
        if ($name instanceof Identifier || $name instanceof Expr) {
            return $name;
        }
        if (\is_string($name)) {
            return new Identifier($name);
        }
        throw new \LogicException('Expected string or instance of Node\\Identifier or Node\\Expr');
    }
    public static function normalizeName($name) : Name
    {
        if ($name instanceof Name) {
            return $name;
        }
        if (\is_string($name)) {
            if (!$name) {
                throw new \LogicException('Name cannot be empty');
            }
            if ($name[0] === '\\') {
                return new Name\FullyQualified(\substr($name, 1));
            }
            if (0 === \strpos($name, 'namespace\\')) {
                return new Name\Relative(\substr($name, \strlen('namespace\\')));
            }
            return new Name($name);
        }
        throw new \LogicException('Name must be a string or an instance of Node\\Name');
    }
    public static function normalizeNameOrExpr($name)
    {
        if ($name instanceof Expr) {
            return $name;
        }
        if (!\is_string($name) && !$name instanceof Name) {
            throw new \LogicException('Name must be a string or an instance of Node\\Name or Node\\Expr');
        }
        return self::normalizeName($name);
    }
    public static function normalizeType($type)
    {
        if (!\is_string($type)) {
            if (!$type instanceof Name && !$type instanceof Identifier && !$type instanceof ComplexType) {
                throw new \LogicException('Type must be a string, or an instance of Name, Identifier or ComplexType');
            }
            return $type;
        }
        $nullable = \false;
        if (\strlen($type) > 0 && $type[0] === '?') {
            $nullable = \true;
            $type = \substr($type, 1);
        }
        $builtinTypes = ['array', 'callable', 'bool', 'int', 'float', 'string', 'iterable', 'void', 'object', 'null', 'false', 'mixed', 'never', 'true'];
        $lowerType = \strtolower($type);
        if (\in_array($lowerType, $builtinTypes)) {
            $type = new Identifier($lowerType);
        } else {
            $type = self::normalizeName($type);
        }
        $notNullableTypes = ['void', 'mixed', 'never'];
        if ($nullable && \in_array((string) $type, $notNullableTypes)) {
            throw new \LogicException(\sprintf('%s type cannot be nullable', $type));
        }
        return $nullable ? new NullableType($type) : $type;
    }
    public static function normalizeValue($value) : Expr
    {
        if ($value instanceof Node\Expr) {
            return $value;
        }
        if (\is_null($value)) {
            return new Expr\ConstFetch(new Name('null'));
        }
        if (\is_bool($value)) {
            return new Expr\ConstFetch(new Name($value ? 'true' : 'false'));
        }
        if (\is_int($value)) {
            return new Scalar\LNumber($value);
        }
        if (\is_float($value)) {
            return new Scalar\DNumber($value);
        }
        if (\is_string($value)) {
            return new Scalar\String_($value);
        }
        if (\is_array($value)) {
            $items = [];
            $lastKey = -1;
            foreach ($value as $itemKey => $itemValue) {
                if (null !== $lastKey && ++$lastKey === $itemKey) {
                    $items[] = new Expr\ArrayItem(self::normalizeValue($itemValue));
                } else {
                    $lastKey = null;
                    $items[] = new Expr\ArrayItem(self::normalizeValue($itemValue), self::normalizeValue($itemKey));
                }
            }
            return new Expr\Array_($items);
        }
        throw new \LogicException('Invalid value');
    }
    public static function normalizeDocComment($docComment) : Comment\Doc
    {
        if ($docComment instanceof Comment\Doc) {
            return $docComment;
        }
        if (\is_string($docComment)) {
            return new Comment\Doc($docComment);
        }
        throw new \LogicException('Doc comment must be a string or an instance of PhpParser\\Comment\\Doc');
    }
    public static function normalizeAttribute($attribute) : Node\AttributeGroup
    {
        if ($attribute instanceof Node\AttributeGroup) {
            return $attribute;
        }
        if (!$attribute instanceof Node\Attribute) {
            throw new \LogicException('Attribute must be an instance of PhpParser\\Node\\Attribute or PhpParser\\Node\\AttributeGroup');
        }
        return new Node\AttributeGroup([$attribute]);
    }
    public static function addModifier(int $modifiers, int $modifier) : int
    {
        Stmt\Class_::verifyModifier($modifiers, $modifier);
        return $modifiers | $modifier;
    }
    public static function addClassModifier(int $existingModifiers, int $modifierToSet) : int
    {
        Stmt\Class_::verifyClassModifier($existingModifiers, $modifierToSet);
        return $existingModifiers | $modifierToSet;
    }
}
