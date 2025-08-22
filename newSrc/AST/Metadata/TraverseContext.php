<?php

declare(strict_types=1);

namespace newSrc\AST\Metadata;

final readonly class TraverseContext
{
    public function __construct(
        public string $filePathname,
    ) {
    }
}
