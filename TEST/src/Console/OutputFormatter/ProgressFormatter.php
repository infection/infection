<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console\OutputFormatter;

use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\ProgressBar;
final class ProgressFormatter extends AbstractOutputFormatter
{
    public function __construct(private ProgressBar $progressBar)
    {
    }
    public function start(int $mutationCount) : void
    {
        parent::start($mutationCount);
        $this->progressBar->start($mutationCount);
    }
    public function advance(MutantExecutionResult $executionResult, int $mutationCount) : void
    {
        parent::advance($executionResult, $mutationCount);
        $this->progressBar->advance();
    }
    public function finish() : void
    {
        parent::finish();
        $this->progressBar->finish();
    }
}
