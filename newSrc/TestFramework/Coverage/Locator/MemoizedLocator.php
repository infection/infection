<?php

declare(strict_types=1);

namespace newSrc\TestFramework\Coverage\Locator;

final class MemoizedLocator implements ReportLocator
{
    private string $location;

    public function __construct(
        private readonly ReportLocator $decoratedLocator,
    ) {
    }

    public function locate(): string
    {
        if (!isset($this->location)) {
            $this->location = $this->decoratedLocator->locate();
        }

        return $this->location;
    }
}
