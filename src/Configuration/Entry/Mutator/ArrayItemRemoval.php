<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry\Mutator;

use Webmozart\Assert\Assert;

final class ArrayItemRemoval implements MutatorConfiguration
{
    private $enabled;
    private $ignore;
    private $settings;

    /**
     * @param string[] $ignore
     */
    public function __construct(bool $enabled, array $ignore, ArrayItemRemovalSettings $settings)
    {
        Assert::allString($ignore);

        $this->enabled = $enabled;
        $this->ignore = $ignore;
        $this->settings = $settings;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string[]
     */
    public function getIgnore(): array
    {
        return $this->ignore;
    }

    public function getSettings(): ArrayItemRemovalSettings
    {
        return $this->settings;
    }
}