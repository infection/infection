<?php

declare(strict_types=1);

namespace newSrc\Trace\Symbol;

final readonly class FunctionReference implements Symbol
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
