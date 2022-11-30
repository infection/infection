<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process\Factory;

use _HumbugBox9658796bb9f0\Composer\InstalledVersions;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\Event\EventDispatcher\EventDispatcher;
use _HumbugBox9658796bb9f0\Infection\Event\MutantProcessWasFinished;
use _HumbugBox9658796bb9f0\Infection\Mutant\Mutant;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResultFactory;
use _HumbugBox9658796bb9f0\Infection\Process\MutantProcess;
use function method_exists;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
use function version_compare;
class MutantProcessFactory
{
    public function __construct(private TestFrameworkAdapter $testFrameworkAdapter, private float $timeout, private EventDispatcher $eventDispatcher, private MutantExecutionResultFactory $resultFactory)
    {
    }
    public function createProcessForMutant(Mutant $mutant, string $testFrameworkExtraOptions = '') : MutantProcess
    {
        $process = new Process($this->testFrameworkAdapter->getMutantCommandLine($mutant->getTests(), $mutant->getFilePath(), $mutant->getMutation()->getHash(), $mutant->getMutation()->getOriginalFilePath(), $testFrameworkExtraOptions));
        $process->setTimeout($this->timeout);
        if (method_exists($process, 'inheritEnvironmentVariables') && version_compare((string) InstalledVersions::getPrettyVersion('symfony/console'), 'v4.4', '<')) {
            $process->inheritEnvironmentVariables();
        }
        $mutantProcess = new MutantProcess($process, $mutant);
        $eventDispatcher = $this->eventDispatcher;
        $resultFactory = $this->resultFactory;
        $mutantProcess->registerTerminateProcessClosure(static function () use($mutantProcess, $eventDispatcher, $resultFactory) : void {
            $eventDispatcher->dispatch(new MutantProcessWasFinished($resultFactory->createFromProcess($mutantProcess)));
        });
        return $mutantProcess;
    }
}
