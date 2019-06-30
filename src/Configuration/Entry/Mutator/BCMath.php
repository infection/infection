<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry\Mutator;

use Infection\Configuration\Entry\BCMathSettings;
use Webmozart\Assert\Assert;

final class BCMath implements MutatorConfiguration
{
    private $enabled;
    private $ignore;
    private $settings;

    /**
     * @param string[] $ignore
     */
    public function __construct(bool $enabled, array $ignore, BCMathSettings $settings)
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

    public function getSettings(): BCMathSettings
    {
        return $this->settings;
    }
}