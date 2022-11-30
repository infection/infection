<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;

use _HumbugBox9658796bb9f0\PhpParser\Error;
use _HumbugBox9658796bb9f0\PhpParser\Node;
class Class_ extends ClassLike
{
    const MODIFIER_PUBLIC = 1;
    const MODIFIER_PROTECTED = 2;
    const MODIFIER_PRIVATE = 4;
    const MODIFIER_STATIC = 8;
    const MODIFIER_ABSTRACT = 16;
    const MODIFIER_FINAL = 32;
    const MODIFIER_READONLY = 64;
    const VISIBILITY_MODIFIER_MASK = 7;
    public $flags;
    public $extends;
    public $implements;
    public function __construct($name, array $subNodes = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->flags = $subNodes['flags'] ?? $subNodes['type'] ?? 0;
        $this->name = \is_string($name) ? new Node\Identifier($name) : $name;
        $this->extends = $subNodes['extends'] ?? null;
        $this->implements = $subNodes['implements'] ?? [];
        $this->stmts = $subNodes['stmts'] ?? [];
        $this->attrGroups = $subNodes['attrGroups'] ?? [];
    }
    public function getSubNodeNames() : array
    {
        return ['attrGroups', 'flags', 'name', 'extends', 'implements', 'stmts'];
    }
    public function isAbstract() : bool
    {
        return (bool) ($this->flags & self::MODIFIER_ABSTRACT);
    }
    public function isFinal() : bool
    {
        return (bool) ($this->flags & self::MODIFIER_FINAL);
    }
    public function isReadonly() : bool
    {
        return (bool) ($this->flags & self::MODIFIER_READONLY);
    }
    public function isAnonymous() : bool
    {
        return null === $this->name;
    }
    public static function verifyClassModifier($a, $b)
    {
        if ($a & self::MODIFIER_ABSTRACT && $b & self::MODIFIER_ABSTRACT) {
            throw new Error('Multiple abstract modifiers are not allowed');
        }
        if ($a & self::MODIFIER_FINAL && $b & self::MODIFIER_FINAL) {
            throw new Error('Multiple final modifiers are not allowed');
        }
        if ($a & self::MODIFIER_READONLY && $b & self::MODIFIER_READONLY) {
            throw new Error('Multiple readonly modifiers are not allowed');
        }
        if ($a & 48 && $b & 48) {
            throw new Error('Cannot use the final modifier on an abstract class');
        }
    }
    public static function verifyModifier($a, $b)
    {
        if ($a & self::VISIBILITY_MODIFIER_MASK && $b & self::VISIBILITY_MODIFIER_MASK) {
            throw new Error('Multiple access type modifiers are not allowed');
        }
        if ($a & self::MODIFIER_ABSTRACT && $b & self::MODIFIER_ABSTRACT) {
            throw new Error('Multiple abstract modifiers are not allowed');
        }
        if ($a & self::MODIFIER_STATIC && $b & self::MODIFIER_STATIC) {
            throw new Error('Multiple static modifiers are not allowed');
        }
        if ($a & self::MODIFIER_FINAL && $b & self::MODIFIER_FINAL) {
            throw new Error('Multiple final modifiers are not allowed');
        }
        if ($a & self::MODIFIER_READONLY && $b & self::MODIFIER_READONLY) {
            throw new Error('Multiple readonly modifiers are not allowed');
        }
        if ($a & 48 && $b & 48) {
            throw new Error('Cannot use the final modifier on an abstract class member');
        }
    }
    public function getType() : string
    {
        return 'Stmt_Class';
    }
}
