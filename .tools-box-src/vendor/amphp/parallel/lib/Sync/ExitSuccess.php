<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

final class ExitSuccess implements ExitResult
{
    private $result;
    public function __construct($result)
    {
        $this->result = $result;
    }
    public function getResult()
    {
        return $this->result;
    }
}
