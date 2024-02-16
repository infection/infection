<?php

namespace Stream_Wrapper_Execution;

class SourceClass
{
    private $finalClass;

    public function __construct(FinalClass $finalClass)
    {
        $this->finalClass = $finalClass;
    }

    public function getOne(): int
    {
        return $this->finalClass->get();
    }
}
