<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;

use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\Node\ComplexType;
use _HumbugBox9658796bb9f0\PhpParser\Node\Identifier;
use _HumbugBox9658796bb9f0\PhpParser\Node\Name;
class Property extends Node\Stmt
{
    public $flags;
    public $props;
    public $type;
    public $attrGroups;
    public function __construct(int $flags, array $props, array $attributes = [], $type = null, array $attrGroups = [])
    {
        $this->attributes = $attributes;
        $this->flags = $flags;
        $this->props = $props;
        $this->type = \is_string($type) ? new Identifier($type) : $type;
        $this->attrGroups = $attrGroups;
    }
    public function getSubNodeNames() : array
    {
        return ['attrGroups', 'flags', 'type', 'props'];
    }
    public function isPublic() : bool
    {
        return ($this->flags & Class_::MODIFIER_PUBLIC) !== 0 || ($this->flags & Class_::VISIBILITY_MODIFIER_MASK) === 0;
    }
    public function isProtected() : bool
    {
        return (bool) ($this->flags & Class_::MODIFIER_PROTECTED);
    }
    public function isPrivate() : bool
    {
        return (bool) ($this->flags & Class_::MODIFIER_PRIVATE);
    }
    public function isStatic() : bool
    {
        return (bool) ($this->flags & Class_::MODIFIER_STATIC);
    }
    public function isReadonly() : bool
    {
        return (bool) ($this->flags & Class_::MODIFIER_READONLY);
    }
    public function getType() : string
    {
        return 'Stmt_Property';
    }
}
