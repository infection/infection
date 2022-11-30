<?php

namespace _HumbugBoxb47773b41c19\Amp;

class CancelledException extends \Exception
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct("The operation was cancelled", 0, $previous);
    }
}
