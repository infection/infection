<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Entry;

final class PhpUnit
{
    private ?string $configDir;
    private ?string $customPath;
    public function __construct(?string $configDir, ?string $executablePath)
    {
        $this->configDir = $configDir;
        $this->customPath = $executablePath;
    }
    public function setConfigDir(string $dir) : void
    {
        $this->configDir = $dir;
    }
    public function getConfigDir() : ?string
    {
        return $this->configDir;
    }
    public function getCustomPath() : ?string
    {
        return $this->customPath;
    }
}
