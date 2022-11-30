<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

class FrameStub extends EnumStub
{
    public $keepArgs;
    public $inTraceStub;
    public function __construct(array $frame, bool $keepArgs = \true, bool $inTraceStub = \false)
    {
        $this->value = $frame;
        $this->keepArgs = $keepArgs;
        $this->inTraceStub = $inTraceStub;
    }
}
