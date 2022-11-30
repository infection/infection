<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node;

use _HumbugBoxb47773b41c19\PhpParser\NodeAbstract;
class Name extends NodeAbstract
{
    public $parts;
    private static $specialClassNames = ['self' => \true, 'parent' => \true, 'static' => \true];
    public function __construct($name, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->parts = self::prepareName($name);
    }
    public function getSubNodeNames() : array
    {
        return ['parts'];
    }
    public function getFirst() : string
    {
        return $this->parts[0];
    }
    public function getLast() : string
    {
        return $this->parts[\count($this->parts) - 1];
    }
    public function isUnqualified() : bool
    {
        return 1 === \count($this->parts);
    }
    public function isQualified() : bool
    {
        return 1 < \count($this->parts);
    }
    public function isFullyQualified() : bool
    {
        return \false;
    }
    public function isRelative() : bool
    {
        return \false;
    }
    public function toString() : string
    {
        return \implode('\\', $this->parts);
    }
    public function toCodeString() : string
    {
        return $this->toString();
    }
    public function toLowerString() : string
    {
        return \strtolower(\implode('\\', $this->parts));
    }
    public function isSpecialClassName() : bool
    {
        return \count($this->parts) === 1 && isset(self::$specialClassNames[\strtolower($this->parts[0])]);
    }
    public function __toString() : string
    {
        return \implode('\\', $this->parts);
    }
    public function slice(int $offset, int $length = null)
    {
        $numParts = \count($this->parts);
        $realOffset = $offset < 0 ? $offset + $numParts : $offset;
        if ($realOffset < 0 || $realOffset > $numParts) {
            throw new \OutOfBoundsException(\sprintf('Offset %d is out of bounds', $offset));
        }
        if (null === $length) {
            $realLength = $numParts - $realOffset;
        } else {
            $realLength = $length < 0 ? $length + $numParts - $realOffset : $length;
            if ($realLength < 0 || $realLength > $numParts - $realOffset) {
                throw new \OutOfBoundsException(\sprintf('Length %d is out of bounds', $length));
            }
        }
        if ($realLength === 0) {
            return null;
        }
        return new static(\array_slice($this->parts, $realOffset, $realLength), $this->attributes);
    }
    public static function concat($name1, $name2, array $attributes = [])
    {
        if (null === $name1 && null === $name2) {
            return null;
        } elseif (null === $name1) {
            return new static(self::prepareName($name2), $attributes);
        } elseif (null === $name2) {
            return new static(self::prepareName($name1), $attributes);
        } else {
            return new static(\array_merge(self::prepareName($name1), self::prepareName($name2)), $attributes);
        }
    }
    private static function prepareName($name) : array
    {
        if (\is_string($name)) {
            if ('' === $name) {
                throw new \InvalidArgumentException('Name cannot be empty');
            }
            return \explode('\\', $name);
        } elseif (\is_array($name)) {
            if (empty($name)) {
                throw new \InvalidArgumentException('Name cannot be empty');
            }
            return $name;
        } elseif ($name instanceof self) {
            return $name->parts;
        }
        throw new \InvalidArgumentException('Expected string, array of parts or Name instance');
    }
    public function getType() : string
    {
        return 'Name';
    }
}
