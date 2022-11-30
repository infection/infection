<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Metrics;

use Generator;
use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\Logs;
use _HumbugBox9658796bb9f0\Infection\Console\LogVerbosity;
use _HumbugBox9658796bb9f0\Infection\Logger\TextFileLogger;
use _HumbugBox9658796bb9f0\Infection\Mutant\DetectionStatus;
use function iterator_to_array;
use function _HumbugBox9658796bb9f0\Safe\array_flip;
class TargetDetectionStatusesProvider
{
    public function __construct(private Logs $logConfig, private string $logVerbosity, private bool $onlyCoveredMode, private bool $showMutations)
    {
    }
    public function get() : array
    {
        return array_flip(iterator_to_array($this->findRequired(), \false));
    }
    private function findRequired() : Generator
    {
        if ($this->showMutations) {
            (yield DetectionStatus::ESCAPED);
        }
        $strykerConfig = $this->logConfig->getStrykerConfig();
        $isStrykerFullReportEnabled = $strykerConfig !== null && $strykerConfig->isForFullReport();
        if ($isStrykerFullReportEnabled) {
            yield from DetectionStatus::ALL;
            return;
        }
        if ($this->logVerbosity === LogVerbosity::NONE) {
            return;
        }
        if ($this->logConfig->getDebugLogFilePath() !== null) {
            yield from DetectionStatus::ALL;
            return;
        }
        if ($this->logConfig->getPerMutatorFilePath() !== null) {
            yield from DetectionStatus::ALL;
            return;
        }
        if ($this->logConfig->getHtmlLogFilePath() !== null) {
            yield from DetectionStatus::ALL;
            return;
        }
        if ($this->logConfig->getUseGitHubAnnotationsLogger()) {
            (yield DetectionStatus::ESCAPED);
        }
        if ($this->logConfig->getJsonLogFilePath() !== null) {
            (yield DetectionStatus::KILLED);
            (yield DetectionStatus::ESCAPED);
            (yield DetectionStatus::ERROR);
            (yield DetectionStatus::SYNTAX_ERROR);
            (yield DetectionStatus::TIMED_OUT);
            if (!$this->onlyCoveredMode) {
                (yield DetectionStatus::NOT_COVERED);
            }
            (yield DetectionStatus::IGNORED);
        }
        if ($this->logConfig->getTextLogFilePath() !== null) {
            (yield DetectionStatus::ESCAPED);
            (yield DetectionStatus::TIMED_OUT);
            (yield DetectionStatus::SKIPPED);
            (yield DetectionStatus::SYNTAX_ERROR);
            if ($this->logVerbosity === LogVerbosity::DEBUG) {
                (yield DetectionStatus::KILLED);
                (yield DetectionStatus::ERROR);
            }
            if (!$this->onlyCoveredMode) {
                (yield DetectionStatus::NOT_COVERED);
            }
            (yield DetectionStatus::IGNORED);
        }
    }
}
