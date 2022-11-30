<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser;

interface Node
{
    public function getType() : string;
    public function getSubNodeNames() : array;
    public function getLine() : int;
    public function getStartLine() : int;
    public function getEndLine() : int;
    public function getStartTokenPos() : int;
    public function getEndTokenPos() : int;
    public function getStartFilePos() : int;
    public function getEndFilePos() : int;
    public function getComments() : array;
    public function getDocComment();
    public function setDocComment(Comment\Doc $docComment);
    public function setAttribute(string $key, $value);
    public function hasAttribute(string $key) : bool;
    public function getAttribute(string $key, $default = null);
    public function getAttributes() : array;
    public function setAttributes(array $attributes);
}
