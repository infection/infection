<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry;

final class Logs
{
    private $textLogFilePath;
    private $summaryLogFilePath;
    private $debugLogFilePath;
    private $perMutatorFilePath;
    private $badge;

    public function __construct(
        ?string $textLogFilePath,
        ?string $summaryLogFilePath,
        ?string $debugLogFilePath,
        ?string $perMutatorFilePath,
        ?Badge $badge
    ) {
        $this->textLogFilePath = $textLogFilePath;
        $this->summaryLogFilePath = $summaryLogFilePath;
        $this->debugLogFilePath = $debugLogFilePath;
        $this->perMutatorFilePath = $perMutatorFilePath;
        $this->badge = $badge;
    }

    public function getTextLogFilePath(): ?string
    {
        return $this->textLogFilePath;
    }

    public function getSummaryLogFilePath(): ?string
    {
        return $this->summaryLogFilePath;
    }

    public function getDebugLogFilePath(): ?string
    {
        return $this->debugLogFilePath;
    }

    public function getPerMutatorFilePath(): ?string
    {
        return $this->perMutatorFilePath;
    }

    public function getBadge(): ?Badge
    {
        return $this->badge;
    }
}