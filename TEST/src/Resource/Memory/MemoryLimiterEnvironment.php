<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Resource\Memory;

use _HumbugBox9658796bb9f0\Composer\XdebugHandler\XdebugHandler;
use const PHP_SAPI;
use function _HumbugBox9658796bb9f0\Safe\ini_get;
class MemoryLimiterEnvironment
{
    public function hasMemoryLimitSet() : bool
    {
        return ini_get('memory_limit') !== '-1';
    }
    public function isUsingSystemIni() : bool
    {
        return PHP_SAPI === 'phpdbg' || XdebugHandler::getSkippedVersion() === '';
    }
}
