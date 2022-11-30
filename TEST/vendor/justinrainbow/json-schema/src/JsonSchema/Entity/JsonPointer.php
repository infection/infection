<?php

namespace _HumbugBox9658796bb9f0\JsonSchema\Entity;

use _HumbugBox9658796bb9f0\JsonSchema\Exception\InvalidArgumentException;
class JsonPointer
{
    private $filename;
    private $propertyPaths = array();
    private $fromDefault = \false;
    public function __construct($value)
    {
        if (!\is_string($value)) {
            throw new InvalidArgumentException('Ref value must be a string');
        }
        $splitRef = \explode('#', $value, 2);
        $this->filename = $splitRef[0];
        if (\array_key_exists(1, $splitRef)) {
            $this->propertyPaths = $this->decodePropertyPaths($splitRef[1]);
        }
    }
    private function decodePropertyPaths($propertyPathString)
    {
        $paths = array();
        foreach (\explode('/', \trim($propertyPathString, '/')) as $path) {
            $path = $this->decodePath($path);
            if (\is_string($path) && '' !== $path) {
                $paths[] = $path;
            }
        }
        return $paths;
    }
    private function encodePropertyPaths()
    {
        return \array_map(array($this, 'encodePath'), $this->getPropertyPaths());
    }
    private function decodePath($path)
    {
        return \strtr($path, array('~1' => '/', '~0' => '~', '%25' => '%'));
    }
    private function encodePath($path)
    {
        return \strtr($path, array('/' => '~1', '~' => '~0', '%' => '%25'));
    }
    public function getFilename()
    {
        return $this->filename;
    }
    public function getPropertyPaths()
    {
        return $this->propertyPaths;
    }
    public function withPropertyPaths(array $propertyPaths)
    {
        $new = clone $this;
        $new->propertyPaths = $propertyPaths;
        return $new;
    }
    public function getPropertyPathAsString()
    {
        return \rtrim('#/' . \implode('/', $this->encodePropertyPaths()), '/');
    }
    public function __toString()
    {
        return $this->getFilename() . $this->getPropertyPathAsString();
    }
    public function setFromDefault()
    {
        $this->fromDefault = \true;
    }
    public function fromDefault()
    {
        return $this->fromDefault;
    }
}
