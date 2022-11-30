<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process\Runner;

use Exception;
use function implode;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
final class InitialTestsFailed extends Exception
{
    public static function fromProcessAndAdapter(Process $initialTestSuiteProcess, TestFrameworkAdapter $testFrameworkAdapter) : self
    {
        $testFrameworkKey = $testFrameworkAdapter->getName();
        $lines = ['Project tests must be in a passing state before running Infection.', $testFrameworkAdapter->getInitialTestsFailRecommendations($initialTestSuiteProcess->getCommandLine()), sprintf('%s reported an exit code of %d.', $testFrameworkKey, $initialTestSuiteProcess->getExitCode()), sprintf('Refer to the %s\'s output below:', $testFrameworkKey)];
        $stdOut = $initialTestSuiteProcess->getOutput();
        if ($stdOut !== '') {
            $lines[] = 'STDOUT:';
            $lines[] = $stdOut;
        }
        $stdError = $initialTestSuiteProcess->getErrorOutput();
        if ($stdError !== '') {
            $lines[] = 'STDERR:';
            $lines[] = $stdError;
        }
        return new self(implode("\n", $lines));
    }
}
