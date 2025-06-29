<?php

namespace newSrc\Reporter;

use newSrc\Engine\Envelope;
use newSrc\MutationAnalyzer\MutantExecutionResult;

interface Reporter
{
    public function collect(MutantExecutionResult $result, Envelope $envelope): void;

    public function report(): void;
}
