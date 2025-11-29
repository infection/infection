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
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getSubNodeNames(): array
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getLine(): int
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getStartLine(): int
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getEndLine(): int
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getStartTokenPos(): int
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getEndTokenPos(): int
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getStartFilePos(): int
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getEndFilePos(): int
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getComments(): array
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getDocComment(): ?Comment\Doc
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function setDocComment(Comment\Doc $docComment): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function setAttribute(string $key, $value): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function hasAttribute(string $key): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getAttribute(string $key, $default = null): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getAttributes(): array
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function setAttributes(array $attributes): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}
