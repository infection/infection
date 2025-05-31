<?php

declare(strict_types=1);


namespace Infection\StaticAnalysis\PHPStan\Mutant;


use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\MutantExecutionResultFactory;
use Infection\Process\MutantProcess;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;
use function sprintf;

final class PHPStanMutantExecutionResultFactory implements MutantExecutionResultFactory
{
    private const PROCESS_MIN_ERROR_CODE = 100;

    public function createFromProcess(MutantProcess $mutantProcess): MutantExecutionResult
    {
        $process = $mutantProcess->getProcess();
        $mutant = $mutantProcess->getMutant();
        $mutation = $mutant->getMutation();

        return new MutantExecutionResult(
            $process->getCommandLine(),
            $this->retrieveProcessOutput($process),
            $this->retrieveDetectionStatus($mutantProcess),
            $mutant->getDiff(),
            $mutation->getHash(),
            $mutation->getMutatorClass(),
            $mutation->getMutatorName(),
            $mutation->getOriginalFilePath(),
            $mutation->getOriginalStartingLine(),
            $mutation->getOriginalEndingLine(),
            $mutation->getOriginalStartFilePosition(),
            $mutation->getOriginalEndFilePosition(),
            $mutant->getPrettyPrintedOriginalCode(),
            $mutant->getMutatedCode(),
            $mutant->getTests(),
        );
    }

    private function retrieveProcessOutput(Process $process): string
    {
        Assert::true(
            $process->isTerminated(),
            sprintf(
                'Cannot retrieve a non-terminated process output. Got "%s"',
                $process->getStatus(),
            ),
        );

        return $process->getOutput() . "\n\n" . $process->getErrorOutput();
    }

    private function retrieveDetectionStatus(MutantProcess $mutantProcess): string
    {
        if ($mutantProcess->isTimedOut()) {
            return DetectionStatus::TIMED_OUT;
        }

        $process = $mutantProcess->getProcess();

        if ($process->getExitCode() > self::PROCESS_MIN_ERROR_CODE) {
            // See \Symfony\Component\Process\Process::$exitCodes
            return DetectionStatus::ERROR;
        }

        if ($process->getExitCode() === 0) {
            return DetectionStatus::ESCAPED;
        }

        return DetectionStatus::KILLED;
    }
}
