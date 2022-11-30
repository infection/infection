<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

interface TaskFailureThrowable extends \Throwable
{
    public function getOriginalClassName() : string;
    public function getOriginalMessage() : string;
    public function getOriginalCode();
    public function getOriginalTrace() : array;
    public function getOriginalTraceAsString() : string;
}
