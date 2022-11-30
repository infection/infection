<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Resource\Time;

use function microtime;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class Stopwatch
{
    private ?float $microTime = null;
    public function start() : void
    {
        Assert::null($this->microTime, 'Timer can not be started again without stopping.');
        $this->microTime = microtime(\true);
    }
    public function stop() : float
    {
        Assert::notNull($this->microTime, 'Timer must be started before stopping.');
        $microTime = $this->microTime;
        $this->microTime = null;
        return microtime(\true) - $microTime;
    }
}
