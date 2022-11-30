<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use ArrayIterator;
use IteratorAggregate;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
use function array_key_exists;
use function implode;
/**
@psalm-immutable
@template-implements
*/
abstract class AggregatedType implements Type, IteratorAggregate
{
    /**
    @psalm-allow-private-mutation
    */
    private array $types = [];
    private string $token;
    public function __construct(array $types, string $token)
    {
        foreach ($types as $type) {
            $this->add($type);
        }
        $this->token = $token;
    }
    public function get(int $index) : ?Type
    {
        if (!$this->has($index)) {
            return null;
        }
        return $this->types[$index];
    }
    public function has(int $index) : bool
    {
        return array_key_exists($index, $this->types);
    }
    public function contains(Type $type) : bool
    {
        foreach ($this->types as $typePart) {
            if ((string) $typePart === (string) $type) {
                return \true;
            }
        }
        return \false;
    }
    public function __toString() : string
    {
        return implode($this->token, $this->types);
    }
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->types);
    }
    /**
    @psalm-suppress
    */
    private function add(Type $type) : void
    {
        if ($type instanceof self) {
            foreach ($type->getIterator() as $subType) {
                $this->add($subType);
            }
            return;
        }
        if ($this->contains($type)) {
            return;
        }
        $this->types[] = $type;
    }
}
