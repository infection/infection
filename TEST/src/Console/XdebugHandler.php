<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console;

use _HumbugBox9658796bb9f0\Composer\XdebugHandler\XdebugHandler as ComposerXdebugHandler;
use _HumbugBox9658796bb9f0\Infection\CannotBeInstantiated;
use _HumbugBox9658796bb9f0\Psr\Log\LoggerInterface;
final class XdebugHandler
{
    use CannotBeInstantiated;
    private const PREFIX = 'INFECTION';
    public static function check(LoggerInterface $logger) : void
    {
        (new ComposerXdebugHandler(self::PREFIX))->setLogger($logger)->setPersistent()->check();
    }
}
