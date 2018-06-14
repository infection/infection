<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Performance\Time;

/**
 * @internal
 */
final class Timer
{
    /**
     * @var float|null
     */
    private $microTime;

    public function start()
    {
        if (null !== $this->microTime) {
            throw TimerIsAlreadyStartedException::create();
        }

        $this->microTime = microtime(true);
    }

    public function stop(): float
    {
        if (null === $this->microTime) {
            throw TimerNotStartedException::create();
        }

        $microTime = $this->microTime;
        $this->microTime = null;

        return microtime(true) - $microTime;
    }
}
