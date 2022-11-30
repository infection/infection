<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process;

use function array_merge;
use _HumbugBox9658796bb9f0\Composer\XdebugHandler\PhpConfig;
use _HumbugBox9658796bb9f0\Composer\XdebugHandler\XdebugHandler;
use function extension_loaded;
use const PHP_SAPI;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
final class OriginalPhpProcess extends Process
{
    public function start(?callable $callback = null, ?array $env = null) : void
    {
        $phpConfig = new PhpConfig();
        $phpConfig->useOriginal();
        if (self::shallExtendEnvironmentWithXdebugMode()) {
            $env = array_merge($env ?? [], ['XDEBUG_MODE' => 'coverage']);
        }
        parent::start($callback, $env ?? []);
        $phpConfig->usePersistent();
    }
    private static function shallExtendEnvironmentWithXdebugMode() : bool
    {
        if (extension_loaded('pcov') || PHP_SAPI === 'phpdbg') {
            return \false;
        }
        return \true;
    }
}
