<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process\Runner;

use _HumbugBox9658796bb9f0\Infection\Differ\DiffSourceCodeMatcher;
use _HumbugBox9658796bb9f0\Infection\Event\EventDispatcher\EventDispatcher;
use _HumbugBox9658796bb9f0\Infection\Event\MutantProcessWasFinished;
use _HumbugBox9658796bb9f0\Infection\Event\MutationTestingWasFinished;
use _HumbugBox9658796bb9f0\Infection\Event\MutationTestingWasStarted;
use _HumbugBox9658796bb9f0\Infection\IterableCounter;
use _HumbugBox9658796bb9f0\Infection\Mutant\Mutant;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantFactory;
use _HumbugBox9658796bb9f0\Infection\Mutation\Mutation;
use _HumbugBox9658796bb9f0\Infection\Process\Factory\MutantProcessFactory;
use function _HumbugBox9658796bb9f0\Pipeline\take;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
class MutationTestingRunner
{
    public function __construct(private MutantProcessFactory $processFactory, private MutantFactory $mutantFactory, private ProcessRunner $processRunner, private EventDispatcher $eventDispatcher, private Filesystem $fileSystem, private DiffSourceCodeMatcher $diffSourceCodeMatcher, private bool $runConcurrently, private float $timeout, private array $ignoreSourceCodeMutatorsMap)
    {
    }
    public function run(iterable $mutations, string $testFrameworkExtraOptions) : void
    {
        $numberOfMutants = IterableCounter::bufferAndCountIfNeeded($mutations, $this->runConcurrently);
        $this->eventDispatcher->dispatch(new MutationTestingWasStarted($numberOfMutants));
        $processes = take($mutations)->cast(fn(Mutation $mutation): Mutant => $this->mutantFactory->create($mutation))->filter(function (Mutant $mutant) : bool {
            $mutatorName = $mutant->getMutation()->getMutatorName();
            foreach ($this->ignoreSourceCodeMutatorsMap[$mutatorName] ?? [] as $sourceCodeRegex) {
                if ($this->diffSourceCodeMatcher->matches($mutant->getDiff()->get(), $sourceCodeRegex)) {
                    $this->eventDispatcher->dispatch(new MutantProcessWasFinished(MutantExecutionResult::createFromIgnoredMutant($mutant)));
                    return \false;
                }
            }
            return \true;
        })->filter(function (Mutant $mutant) : bool {
            if ($mutant->isCoveredByTest()) {
                return \true;
            }
            $this->eventDispatcher->dispatch(new MutantProcessWasFinished(MutantExecutionResult::createFromNonCoveredMutant($mutant)));
            return \false;
        })->filter(function (Mutant $mutant) : bool {
            if ($mutant->getMutation()->getNominalTestExecutionTime() < $this->timeout) {
                return \true;
            }
            $this->eventDispatcher->dispatch(new MutantProcessWasFinished(MutantExecutionResult::createFromTimeSkippedMutant($mutant)));
            return \false;
        })->cast(function (Mutant $mutant) use($testFrameworkExtraOptions) : ProcessBearer {
            $this->fileSystem->dumpFile($mutant->getFilePath(), $mutant->getMutatedCode()->get());
            return $this->processFactory->createProcessForMutant($mutant, $testFrameworkExtraOptions);
        });
        $this->processRunner->run($processes);
        $this->eventDispatcher->dispatch(new MutationTestingWasFinished());
    }
}
