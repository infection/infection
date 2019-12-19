<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\PhpParser;

use PhpParser\Comment\Doc;
use LogicException;
use PhpParser\Comment;
use PhpParser\Node;

final class FakeNode implements Node
{
    public function getType(): string
    {
        throw new LogicException();
    }

    public function getSubNodeNames(): array
    {
        throw new LogicException();
    }

    public function getLine(): int
    {
        throw new LogicException();
    }

    public function getStartLine(): int
    {
        throw new LogicException();
    }

    public function getEndLine(): int
    {
        throw new LogicException();
    }

    public function getStartTokenPos(): int
    {
        throw new LogicException();
    }

    public function getEndTokenPos(): int
    {
        throw new LogicException();
    }

    public function getStartFilePos(): int
    {
        throw new LogicException();
    }

    public function getEndFilePos(): int
    {
        throw new LogicException();
    }

    public function getComments(): array
    {
        throw new LogicException();
    }

    public function getDocComment()
    {
        throw new LogicException();
    }

    public function setDocComment(Doc $docComment)
    {
        throw new LogicException();
    }

    public function setAttribute(string $key, $value)
    {
        throw new LogicException();
    }

    public function hasAttribute(string $key): bool
    {
        throw new LogicException();
    }

    public function getAttribute(string $key, $default = null)
    {
        throw new LogicException();
    }

    public function getAttributes(): array
    {
        throw new LogicException();
    }

    public function setAttributes(array $attributes)
    {
        throw new LogicException();
    }
}
