<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event;

final class InitialTestSuiteWasFinished
{
    public function __construct(private string $outputText)
    {
    }
    public function getOutputText() : string
    {
        return $this->outputText;
    }
}
