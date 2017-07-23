<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\EventDispatcher;

interface ContainsListenersInterface
{
    /**
     * @param $eventName
     * @param callable $listener
     *
     * @return mixed
     */
    public function addListener($eventName, callable $listener);

    /**
     * @param string $eventName
     *
     * @return callable[]
     */
    public function getListeners($eventName);

    /**
     * @param string $eventName
     *
     * @return bool
     */
    public function hasListeners($eventName);
}
