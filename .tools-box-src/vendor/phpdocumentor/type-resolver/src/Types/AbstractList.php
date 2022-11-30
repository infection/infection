<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
/**
@psalm-immutable
*/
abstract class AbstractList implements Type
{
    protected $valueType;
    protected $keyType;
    protected $defaultKeyType;
    public function __construct(?Type $valueType = null, ?Type $keyType = null)
    {
        if ($valueType === null) {
            $valueType = new Mixed_();
        }
        $this->valueType = $valueType;
        $this->defaultKeyType = new Compound([new String_(), new Integer()]);
        $this->keyType = $keyType;
    }
    public function getKeyType() : Type
    {
        return $this->keyType ?? $this->defaultKeyType;
    }
    public function getValueType() : Type
    {
        return $this->valueType;
    }
    public function __toString() : string
    {
        if ($this->keyType) {
            return 'array<' . $this->keyType . ',' . $this->valueType . '>';
        }
        if ($this->valueType instanceof Mixed_) {
            return 'array';
        }
        if ($this->valueType instanceof Compound) {
            return '(' . $this->valueType . ')[]';
        }
        return $this->valueType . '[]';
    }
}
