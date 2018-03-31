<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console\OutputFormatter;

use Infection\Process\MutantProcess;
use Symfony\Component\Console\Output\OutputInterface;

final class DotFormatter extends AbstractOutputFormatter
{
    const DOTS_PER_ROW = 50;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function start(int $mutationCount)
    {
        parent::start($mutationCount);

        $this->output->writeln([
            '',
            '<killed>.</killed>: killed, '
            . '<escaped>M</escaped>: escaped, '
            . '<uncovered>S</uncovered>: uncovered, '
            . '<with-error>E</with-error>: fatal error, '
            . '<timeout>T</timeout>: timed out',
            '',
        ]);
    }

    public function advance(MutantProcess $mutantProcess, int $mutationCount)
    {
        parent::advance($mutantProcess, $mutationCount);

        switch ($mutantProcess->getResultCode()) {
            case MutantProcess::CODE_KILLED:
                $this->output->write('<killed>.</killed>');
                break;
            case MutantProcess::CODE_NOT_COVERED:
                $this->output->write('<uncovered>S</uncovered>');
                break;
            case MutantProcess::CODE_ESCAPED:
                $this->output->write('<escaped>M</escaped>');
                break;
            case MutantProcess::CODE_TIMED_OUT:
                $this->output->write('<timeout>T</timeout>');
                break;
            case MutantProcess::CODE_ERROR:
                $this->output->write('<with-error>E</with-error>');
                break;
        }

        $remainder = $this->callsCount % self::DOTS_PER_ROW;
        $endOfRow = 0 === $remainder;
        $lastDot = $mutationCount === $this->callsCount;

        if ($lastDot && !$endOfRow) {
            $this->output->write(str_repeat(' ', self::DOTS_PER_ROW - $remainder));
        }

        if ($lastDot || $endOfRow) {
            $length = strlen((string) $mutationCount);
            $format = sprintf('   (%%%dd / %%%dd)', $length, $length);

            $this->output->write(sprintf($format, $this->callsCount, $mutationCount));

            if ($this->callsCount !== $mutationCount) {
                $this->output->writeln('');
            }
        }
    }
}
