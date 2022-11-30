<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Resource\Memory;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\MemoryUsageAware;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use const PHP_EOL;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Exception\IOException;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
class MemoryLimiter
{
    public function __construct(private Filesystem $fileSystem, private string $phpIniPath, private MemoryLimiterEnvironment $environment)
    {
    }
    public function limitMemory(string $processOutput, TestFrameworkAdapter $adapter) : void
    {
        if (!$adapter instanceof MemoryUsageAware || $this->environment->hasMemoryLimitSet() || $this->environment->isUsingSystemIni()) {
            return;
        }
        $tmpConfigPath = $this->phpIniPath;
        if ($tmpConfigPath === '') {
            return;
        }
        if (!$this->fileSystem->exists($tmpConfigPath)) {
            return;
        }
        $memoryLimit = $adapter->getMemoryUsed($processOutput);
        if ($memoryLimit === -1.0) {
            return;
        }
        $memoryLimit *= 2;
        try {
            $this->fileSystem->appendToFile($tmpConfigPath, PHP_EOL . sprintf('memory_limit = %dM', $memoryLimit));
        } catch (IOException) {
        }
    }
}
