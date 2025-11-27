<?php

declare(strict_types=1);

namespace Infection\Source;

interface SourceLineFilter
{
    public function contains(
        string $sourceFilePathname,
        int $startLine,
        int $endLine,
    ): bool;
}
