<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework;

/**
 * @internal
 */
abstract class TestFrameworkExtraOptions
{
    /**
     * @var string
     */
    private $extraOptions;

    abstract protected function getInitialRunOnlyOptions(): array;

    public function __construct(string $extraOptions = null)
    {
        $this->extraOptions = $extraOptions ?: '';
    }

    public function getForInitialProcess(): string
    {
        return $this->extraOptions;
    }

    public function getForMutantProcess(): string
    {
        $extraOptions = $this->extraOptions;

        foreach ($this->getInitialRunOnlyOptions() as $initialRunOnlyOption) {
            $extraOptions = preg_replace(sprintf('/%s[\=| ]([^ ]+)/', $initialRunOnlyOption), '', $extraOptions);
        }

        return preg_replace('/\s+/', ' ', trim($extraOptions));
    }
}
