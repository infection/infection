<?php

declare(strict_types=1);

namespace Infection\Git;

// TODO: maybe we don't want memoized results for everything...
final class MemoizedGit implements Git
{
    private string $defaultBase;
    private string $defaultBaseFilter;

    public function __construct(
        private readonly Git $decoratedGit,
    ) {
    }

    public function reset(): void
    {
        unset($this->defaultBase);
        unset($this->defaultBaseFilter);
    }

    public function getDefaultBase(): string
    {
        if (!isset($this->defaultBase)) {
            $this->defaultBase = $this->decoratedGit->getDefaultBase();
        }

        return $this->defaultBase;
    }

    public function getDefaultBaseFilter(): string
    {
        if (!isset($this->defaultBaseFilter)) {
            $this->defaultBaseFilter = $this->decoratedGit->getDefaultBaseFilter();
        }

        return $this->defaultBaseFilter;
    }
}