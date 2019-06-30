<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry;

final class Badge
{
    private $branch;

    public function __construct(string $branch)
    {
        $this->branch = $branch;
    }

    public function getBranch(): string
    {
        return $this->branch;
    }
}