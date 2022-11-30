<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\AbstractTestFramework;

use RuntimeException;
final class UnsupportedTestFrameworkVersion extends RuntimeException
{
    private string $detectedVersion;
    private string $minimumSupportedVersion;
    public function __construct(string $detectedVersion, string $minimumSupportedVersion)
    {
        parent::__construct();
        $this->detectedVersion = $detectedVersion;
        $this->minimumSupportedVersion = $minimumSupportedVersion;
    }
    public function getDetectedVersion() : string
    {
        return $this->detectedVersion;
    }
    public function getMinimumSupportedVersion() : string
    {
        return $this->minimumSupportedVersion;
    }
}
