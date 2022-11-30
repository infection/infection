<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Application;

use function sprintf;
abstract class BaseApplication implements Application
{
    public function getLongVersion() : string
    {
        if ('UNKNOWN' !== $this->getName()) {
            if ('UNKNOWN' !== $this->getVersion()) {
                return sprintf('%s <info>%s</info>', $this->getName(), $this->getVersion());
            }
            return $this->getName();
        }
        return 'Console Tool';
    }
    public function getHelp() : string
    {
        return $this->getLongVersion();
    }
    public function getDefaultCommand() : string
    {
        return 'list';
    }
    public function isAutoExitEnabled() : bool
    {
        return \true;
    }
    public function areExceptionsCaught() : bool
    {
        return \true;
    }
}
