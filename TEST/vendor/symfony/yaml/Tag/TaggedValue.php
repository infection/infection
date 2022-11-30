<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Tag;

final class TaggedValue
{
    private $tag;
    private $value;
    public function __construct(string $tag, $value)
    {
        $this->tag = $tag;
        $this->value = $value;
    }
    public function getTag() : string
    {
        return $this->tag;
    }
    public function getValue()
    {
        return $this->value;
    }
}
