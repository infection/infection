<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync\Internal;

use _HumbugBoxb47773b41c19\Amp\Delayed;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Sync\Lock;
use function _HumbugBoxb47773b41c19\Amp\call;
final class MutexStorage extends \Threaded
{
    public const LATENCY_TIMEOUT = 10;
    private $locked = \false;
    public function acquire() : Promise
    {
        return call(function () : \Generator {
            $tsl = function () : bool {
                if ($this->locked) {
                    return \true;
                }
                $this->locked = \true;
                return \false;
            };
            while ($this->locked || $this->synchronized($tsl)) {
                (yield new Delayed(self::LATENCY_TIMEOUT));
            }
            return new Lock(0, function () : void {
                $this->locked = \false;
            });
        });
    }
}
