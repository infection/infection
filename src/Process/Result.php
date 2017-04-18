<?php

declare(strict_types=1);


namespace Infection\Process;


class Result
{
    const KILLED = 0;

    public function __construct($status, string $processOutput, int $processExitStatus)
    {


    }

    public function isKilled()
    {
        return $this->status === self::KILLED;
    }
}