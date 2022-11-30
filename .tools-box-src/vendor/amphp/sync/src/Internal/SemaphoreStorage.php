<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync\Internal;

use _HumbugBoxb47773b41c19\Amp\Delayed;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Sync\Lock;
use function _HumbugBoxb47773b41c19\Amp\call;
final class SemaphoreStorage extends \Threaded
{
    public const LATENCY_TIMEOUT = 10;
    public function __construct(int $locks)
    {
        foreach (\range(0, $locks - 1) as $lock) {
            $this[] = $lock;
        }
    }
    public function acquire() : Promise
    {
        return call(function () : \Generator {
            $tsl = function () : ?int {
                if (!$this->count()) {
                    return null;
                }
                return $this->shift();
            };
            while (!$this->count() || ($id = $this->synchronized($tsl)) === null) {
                (yield new Delayed(self::LATENCY_TIMEOUT));
            }
            return new Lock($id, function (Lock $lock) : void {
                $id = $lock->getId();
                $this->synchronized(function () use($id) {
                    $this[] = $id;
                });
            });
        });
    }
}
