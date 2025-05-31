<?php

declare(strict_types=1);


namespace Infection\StaticAnalysis\PHPStan\Process;


use Infection\Mutant\Mutant;
use Infection\Process\Factory\LazyMutantProcessFactory;
use Infection\Process\MutantProcess;
use Infection\StaticAnalysis\PHPStan\Mutant\PHPStanMutantExecutionResultFactory;
use Infection\TestFramework\CommandLineBuilder;
use Symfony\Component\Process\Process;
use function var_dump;

final class PHPStanMutantProcessFactory implements LazyMutantProcessFactory
{
    public function __construct(
        private PHPStanMutantExecutionResultFactory $mutantExecutionResultFactory,
        private readonly string $staticAnalysisToolExecutable,
        private readonly CommandLineBuilder $commandLineBuilder,
    ) {
    }

    public function create(Mutant $mutant): MutantProcess
    {
        $process = new Process(
            command: $this->getMutantCommandLine(
                $mutant->getFilePath(),
                $mutant->getMutation()->getOriginalFilePath(),
            ),
            timeout: 30, // todo get from config
        );

        return new MutantProcess(
            $process,
            $mutant,
            $this->mutantExecutionResultFactory,
        );
    }

    private function getMutantCommandLine(
        string $mutatedFilePath,
        string $mutationOriginalFilePath,
    ): array {
        return $this->commandLineBuilder->build(
            $this->staticAnalysisToolExecutable,
            [],
            [
                "--tmp-file=$mutatedFilePath",
                "--instead-of=$mutationOriginalFilePath",
                '--error-format=json',
                '--no-progress',
                '-vv',
                // TODO --stop-on-first-error
            ],
        );
    }
}
