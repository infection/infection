<?php

declare(strict_types=1);

namespace Infection\TestFramework\Tracing;

use Infection\TestFramework\Coverage\NodeLineRangeData;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\TestFramework\Coverage\Trace;
use Symfony\Component\Finder\SplFileInfo;

final class LazyTrace implements Trace
{
    public function __construct(
        public readonly SplFileInfo $sourceFileInfo,
    ) {
    }

    public function getSourceFileInfo(): SplFileInfo
    {
        // TODO: Implement getSourceFileInfo() method.
    }

    public function getRealPath(): string
    {
        // TODO: Implement getRealPath() method.
    }

    public function getRelativePathname(): string
    {
        // TODO: Implement getRelativePathname() method.
    }

    public function hasTests(): bool
    {
        // TODO: Implement hasTests() method.
    }

    public function getTests(): ?TestLocations
    {
        // TODO: Implement getTests() method.
    }

    public function getAllTestsForMutation(
        NodeLineRangeData $lineRange,
        bool $isOnFunctionSignature,
    ): iterable
    {
        // TODO: Implement getAllTestsForMutation() method.
    }
}