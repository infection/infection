<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry;

final class PhpUnit
{
    private $configDir;
    private $customPath;

    public function __construct(?string $configDir, ?string $executablePath)
    {
        $this->configDir = $configDir;
        $this->customPath = $executablePath;
    }

    public function getConfigDir(): ?string
    {
        return $this->configDir;
    }

    public function getCustomPath(): ?string
    {
        return $this->customPath;
    }
}