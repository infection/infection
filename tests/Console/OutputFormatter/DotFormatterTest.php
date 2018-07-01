<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Console\OutputFormatter;

use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class DotFormatterTest extends TestCase
{
    public function test_start_logs_inital_starting_text()
    {
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with([
            '',
            '<killed>.</killed>: killed, '
            . '<escaped>M</escaped>: escaped, '
            . '<uncovered>S</uncovered>: uncovered, '
            . '<with-error>E</with-error>: fatal error, '
            . '<timeout>T</timeout>: timed out',
            '',
        ]);

        $formatter = new DotFormatter($output);
        $formatter->start(10);
    }

    public function test_killed_logs_correctly_in_console()
    {
        $outputKilled = $this->getStartOutputFormatter();
        $outputKilled->expects($this->once())->method('write')->with('<killed>.</killed>');

        $dot = new DotFormatter($outputKilled);
        $dot->start(10);
        $dot->advance($this->getMutantsOfType(MutantProcess::CODE_KILLED)[0], 10);
    }

    public function test_escaped_logs_correctly_in_console()
    {
        $outputEscaped = $this->getStartOutputFormatter();
        $outputEscaped->expects($this->once())->method('write')->with('<escaped>M</escaped>');

        $dot = new DotFormatter($outputEscaped);
        $dot->start(10);
        $dot->advance($this->getMutantsOfType(MutantProcess::CODE_ESCAPED)[0], 10);
    }

    public function test_errored_logs_correctly_in_console()
    {
        $outputErrored = $this->getStartOutputFormatter();
        $outputErrored->expects($this->once())->method('write')->with('<with-error>E</with-error>');

        $dot = new DotFormatter($outputErrored);
        $dot->start(10);
        $dot->advance($this->getMutantsOfType(MutantProcess::CODE_ERROR)[0], 10);
    }

    public function test_timed_out_logs_correctly_in_console()
    {
        $outputTimedOut = $this->getStartOutputFormatter();
        $outputTimedOut->expects($this->once())->method('write')->with('<timeout>T</timeout>');

        $dot = new DotFormatter($outputTimedOut);
        $dot->start(10);
        $dot->advance($this->getMutantsOfType(MutantProcess::CODE_TIMED_OUT)[0], 10);
    }

    public function test_not_covered_correctly_in_console()
    {
        $outputNotcovered = $this->getStartOutputFormatter();
        $outputNotcovered->expects($this->once())->method('write')->with('<uncovered>S</uncovered>');

        $dot = new DotFormatter($outputNotcovered);
        $dot->start(10);
        $dot->advance($this->getMutantsOfType(MutantProcess::CODE_NOT_COVERED)[0], 10);
    }

    private function getMutantsOfType(int $mutantCode, int $count = 1): array
    {
        $mutants = [];

        for ($i = 0; $i < $count; ++$i) {
            $mutant = $this->createMock(MutantProcessInterface::class);
            $mutant->expects($this->once())->method('getResultCode')->willReturn($mutantCode);
            $mutants[] = $mutant;
        }

        return $mutants;
    }

    private function getStartOutputFormatter()
    {
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with([
            '',
            '<killed>.</killed>: killed, '
            . '<escaped>M</escaped>: escaped, '
            . '<uncovered>S</uncovered>: uncovered, '
            . '<with-error>E</with-error>: fatal error, '
            . '<timeout>T</timeout>: timed out',
            '',
        ]);

        return $output;
    }
}
