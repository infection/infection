<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console\OutputFormatter;

use _HumbugBox9658796bb9f0\Infection\Mutant\DetectionStatus;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_repeat;
use function strlen;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class DotFormatter extends AbstractOutputFormatter
{
    private const DOTS_PER_ROW = 50;
    public function __construct(private OutputInterface $output)
    {
    }
    public function start(int $mutationCount) : void
    {
        parent::start($mutationCount);
        $this->output->writeln(['', '<killed>.</killed>: killed, ' . '<escaped>M</escaped>: escaped, ' . '<uncovered>U</uncovered>: uncovered, ' . '<with-error>E</with-error>: fatal error, ' . '<with-syntax-error>X</with-syntax-error>: syntax error, ' . '<timeout>T</timeout>: timed out, ' . '<skipped>S</skipped>: skipped, ' . '<ignored>I</ignored>: ignored', '']);
    }
    public function advance(MutantExecutionResult $executionResult, int $mutationCount) : void
    {
        parent::advance($executionResult, $mutationCount);
        switch ($executionResult->getDetectionStatus()) {
            case DetectionStatus::KILLED:
                $this->output->write('<killed>.</killed>');
                break;
            case DetectionStatus::NOT_COVERED:
                $this->output->write('<uncovered>U</uncovered>');
                break;
            case DetectionStatus::ESCAPED:
                $this->output->write('<escaped>M</escaped>');
                break;
            case DetectionStatus::TIMED_OUT:
                $this->output->write('<timeout>T</timeout>');
                break;
            case DetectionStatus::SKIPPED:
                $this->output->write('<skipped>S</skipped>');
                break;
            case DetectionStatus::ERROR:
                $this->output->write('<with-error>E</with-error>');
                break;
            case DetectionStatus::SYNTAX_ERROR:
                $this->output->write('<with-syntax-error>X</with-syntax-error>');
                break;
            case DetectionStatus::IGNORED:
                $this->output->write('<ignored>I</ignored>');
                break;
        }
        $remainder = $this->callsCount % self::DOTS_PER_ROW;
        $endOfRow = $remainder === 0;
        $lastDot = $mutationCount === $this->callsCount;
        if ($lastDot && !$endOfRow) {
            $this->output->write(str_repeat(' ', self::DOTS_PER_ROW - $remainder));
        }
        if ($lastDot || $endOfRow) {
            if ($mutationCount === self::UNKNOWN_COUNT) {
                $this->output->write(sprintf('   (%5d)', $this->callsCount));
            } else {
                $length = strlen((string) $mutationCount);
                $format = sprintf('   (%%%dd / %%%dd)', $length, $length);
                $this->output->write(sprintf($format, $this->callsCount, $mutationCount));
            }
            if ($this->callsCount !== $mutationCount) {
                $this->output->writeln('');
            }
        }
    }
}
