<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser;

use function is_array;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class MutatedNode
{
    private $value;
    private function __construct($value)
    {
        if (is_array($value)) {
            Assert::allIsInstanceOf($value, Node::class);
        } else {
            Assert::isInstanceOf($value, Node::class);
        }
        $this->value = $value;
    }
    public static function wrap($value) : self
    {
        return new self($value);
    }
    public function unwrap()
    {
        return $this->value;
    }
}
