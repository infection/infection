<?php

declare(strict_types=1);

namespace Infection\Git;

use DomainException;

/**
 * @internal
 */
final class FakeGit implements Git
{
    public function getDefaultBase(): string
    {
        throw new DomainException('Unexpected call.');
    }

    public function getChangedFileRelativePaths(string $diffFilter,
        string $base,
        array $sourceDirectories,): string
    {
        throw new DomainException('Unexpected call.');
    }

    public function getChangedLinesRangesByFileRelativePaths(string $diffFilter,
        string $base,
        array $sourceDirectories,): array
    {
        throw new DomainException('Unexpected call.');
    }

    public function getBaseReference(string $base): string
    {
        throw new DomainException('Unexpected call.');
    }
}