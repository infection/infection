<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Entry;

class Logs
{
    private ?string $textLogFilePath;
    private ?string $htmlLogFilePath;
    private ?string $summaryLogFilePath;
    private ?string $jsonLogFilePath;
    private ?string $debugLogFilePath;
    private ?string $perMutatorFilePath;
    private bool $useGitHubAnnotationsLogger;
    private ?StrykerConfig $strykerConfig;
    public function __construct(?string $textLogFilePath, ?string $htmlLogFilePath, ?string $summaryLogFilePath, ?string $jsonLogFilePath, ?string $debugLogFilePath, ?string $perMutatorFilePath, bool $useGitHubAnnotationsLogger, ?StrykerConfig $strykerConfig)
    {
        $this->textLogFilePath = $textLogFilePath;
        $this->htmlLogFilePath = $htmlLogFilePath;
        $this->summaryLogFilePath = $summaryLogFilePath;
        $this->jsonLogFilePath = $jsonLogFilePath;
        $this->debugLogFilePath = $debugLogFilePath;
        $this->perMutatorFilePath = $perMutatorFilePath;
        $this->useGitHubAnnotationsLogger = $useGitHubAnnotationsLogger;
        $this->strykerConfig = $strykerConfig;
    }
    public static function createEmpty() : self
    {
        return new self(null, null, null, null, null, null, \false, null);
    }
    public function getTextLogFilePath() : ?string
    {
        return $this->textLogFilePath;
    }
    public function getHtmlLogFilePath() : ?string
    {
        return $this->htmlLogFilePath;
    }
    public function setHtmlLogFilePath(string $htmlLogFilePath) : void
    {
        $this->htmlLogFilePath = $htmlLogFilePath;
    }
    public function getSummaryLogFilePath() : ?string
    {
        return $this->summaryLogFilePath;
    }
    public function getJsonLogFilePath() : ?string
    {
        return $this->jsonLogFilePath;
    }
    public function getDebugLogFilePath() : ?string
    {
        return $this->debugLogFilePath;
    }
    public function getPerMutatorFilePath() : ?string
    {
        return $this->perMutatorFilePath;
    }
    public function setUseGitHubAnnotationsLogger(bool $useGitHubAnnotationsLogger) : void
    {
        $this->useGitHubAnnotationsLogger = $useGitHubAnnotationsLogger;
    }
    public function getUseGitHubAnnotationsLogger() : bool
    {
        return $this->useGitHubAnnotationsLogger;
    }
    public function getStrykerConfig() : ?StrykerConfig
    {
        return $this->strykerConfig;
    }
}
