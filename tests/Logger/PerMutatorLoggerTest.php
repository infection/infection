<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Logger;

use Infection\Logger\PerMutatorLogger;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutator\Regex\PregQuote;
use Infection\Mutator\Util\MutatorConfig;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class PerMutatorLoggerTest extends TestCase
{
    public function test_it_correctly_build_log_lines()
    {
        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())
            ->method('dumpFile')
            ->with(
                sys_get_temp_dir() . '/fake-file.md',
                "# Effects per Mutator\n" .
                "\n" .
                "| Mutator | Mutations | Killed | Escaped | Errors | Timed Out | MSI | Covered MSI |\n" .
                "| ------- | --------- | ------ | ------- |------- | --------- | --- | ----------- |\n" .
                "| For_ | 15 | 10 | 0 | 0 | 0 | 67| 100|\n" .
                '| PregQuote | 5 | 0 | 0 | 0 | 0 | 0| 0|'
            );

        $perMutatorLogger = new PerMutatorLogger(
            sys_get_temp_dir() . '/fake-file.md',
            $this->createMetricsCalculator(),
            $fs,
            true,
            true
        );

        $perMutatorLogger->log();
    }

    private function createMetricsCalculator(): MetricsCalculator
    {
        $processes = [];

        for ($i = 0; $i < 10; ++$i) {
            $mutantFor = $this->createMock(MutantProcessInterface::class);
            $mutantFor->expects($this->once())->method('getMutator')->willReturn(new For_(new MutatorConfig([])));
            $mutantFor->expects($this->exactly(2))->method('getResultCode')->willReturn(MutantProcess::CODE_KILLED);
            $processes[] = $mutantFor;
        }

        for ($i = 0; $i < 5; ++$i) {
            $mutantFor = $this->createMock(MutantProcessInterface::class);
            $mutantFor->expects($this->once())->method('getMutator')->willReturn(new For_(new MutatorConfig([])));
            $mutantFor->expects($this->exactly(2))->method('getResultCode')->willReturn(MutantProcess::CODE_NOT_COVERED);
            $processes[] = $mutantFor;
        }

        for ($i = 0; $i < 5; ++$i) {
            $mutantFor = $this->createMock(MutantProcessInterface::class);
            $mutantFor->expects($this->once())->method('getMutator')->willReturn(new PregQuote(new MutatorConfig([])));
            $mutantFor->expects($this->exactly(2))->method('getResultCode')->willReturn(MutantProcess::CODE_NOT_COVERED);
            $processes[] = $mutantFor;
        }

        return MetricsCalculator::createFromArray($processes);
    }
}
