<?php

namespace HumbugBox420\KevinGH\RequirementChecker;

final class Requirement
{
    private $checkIsFulfilled;
    private $fulfilled;
    private $testMessage;
    private $helpText;
    public function __construct(IsFulfilled $checkIsFulfilled, string $testMessage, string $helpText)
    {
        $this->checkIsFulfilled = $checkIsFulfilled;
        $this->testMessage = $testMessage;
        $this->helpText = $helpText;
    }
    public function isFulfilled() : bool
    {
        if (!isset($this->fulfilled)) {
            $this->fulfilled = $this->checkIsFulfilled->__invoke();
        }
        return $this->fulfilled;
    }
    public function getIsFullfilledChecker() : IsFulfilled
    {
        return $this->checkIsFulfilled;
    }
    public function getTestMessage() : string
    {
        return $this->testMessage;
    }
    public function getHelpText() : string
    {
        return $this->helpText;
    }
}
