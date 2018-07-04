<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Performance\Time;

use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Timer
{
    /**
     * @var float|null
     */
    private $microTime;

    public function start(): void
    {
        Assert::null($this->microTime, 'Timer can not be started again without stopping.');

        $this->microTime = microtime(true);
    }

    public function stop(): float
    {
        Assert::notNull($this->microTime, 'Timer must be started before stopping.');

        $microTime = $this->microTime;
        $this->microTime = null;

        return microtime(true) - $microTime;
    }
}
