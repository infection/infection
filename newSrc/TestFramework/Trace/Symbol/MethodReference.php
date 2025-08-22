<?php

declare(strict_types=1);

namespace newSrc\TestFramework\Trace\Symbol;

final readonly class MethodReference implements Symbol
{
    public function __construct(
        private string $name,
    ) {
    }

    public function toString(): string
    {
        return $this->name;
    }
}
