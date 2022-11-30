<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Exception;

class ThrowingCasterException extends \Exception
{
    public function __construct(\Throwable $prev)
    {
        parent::__construct('Unexpected ' . \get_class($prev) . ' thrown from a caster: ' . $prev->getMessage(), 0, $prev);
    }
}
