<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutant;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\SyntaxErrorAware;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\Process\MutantProcess;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class MutantExecutionResultFactory
{
    public function __construct(private TestFrameworkAdapter $testFrameworkAdapter)
    {
    }
    public function createFromProcess(MutantProcess $mutantProcess) : MutantExecutionResult
    {
        $process = $mutantProcess->getProcess();
        $mutant = $mutantProcess->getMutant();
        $mutation = $mutant->getMutation();
        return new MutantExecutionResult($process->getCommandLine(), $this->retrieveProcessOutput($process), $this->retrieveDetectionStatus($mutantProcess), $mutant->getDiff(), $mutation->getHash(), $mutation->getMutatorName(), $mutation->getOriginalFilePath(), $mutation->getOriginalStartingLine(), $mutation->getOriginalEndingLine(), $mutation->getOriginalStartFilePosition(), $mutation->getOriginalEndFilePosition(), $mutant->getPrettyPrintedOriginalCode(), $mutant->getMutatedCode(), $mutant->getTests());
    }
    private function retrieveProcessOutput(Process $process) : string
    {
        Assert::true($process->isTerminated(), sprintf('Cannot retrieve a non-terminated process output. Got "%s"', $process->getStatus()));
        return $process->getOutput();
    }
    private function retrieveDetectionStatus(MutantProcess $mutantProcess) : string
    {
        if (!$mutantProcess->getMutant()->isCoveredByTest()) {
            return DetectionStatus::NOT_COVERED;
        }
        if ($mutantProcess->isTimedOut()) {
            return DetectionStatus::TIMED_OUT;
        }
        $process = $mutantProcess->getProcess();
        if ($process->getExitCode() > 100) {
            return DetectionStatus::ERROR;
        }
        $output = $this->retrieveProcessOutput($process);
        if ($process->getExitCode() === 0 && $this->testFrameworkAdapter->testsPass($output)) {
            return DetectionStatus::ESCAPED;
        }
        if ($this->testFrameworkAdapter instanceof SyntaxErrorAware && $this->testFrameworkAdapter->isSyntaxError($output)) {
            return DetectionStatus::SYNTAX_ERROR;
        }
        return DetectionStatus::KILLED;
    }
}
