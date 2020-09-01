<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use Infection\Tests\UnsupportedMethod;
use PhpParser\Comment;
use PhpParser\Node;

final class FakeNode implements Node
{
    public function getType(): string
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getSubNodeNames(): array
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getLine(): int
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getStartLine(): int
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getEndLine(): int
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getStartTokenPos(): int
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getEndTokenPos(): int
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getStartFilePos(): int
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getEndFilePos(): int
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getComments(): array
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getDocComment()
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function setDocComment(Comment\Doc $docComment)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function setAttribute(string $key, $value)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function hasAttribute(string $key): bool
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getAttribute(string $key, $default = null)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getAttributes(): array
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function setAttributes(array $attributes)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }
}
