<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Application;

use _HumbugBoxb47773b41c19\Fidry\Console\Command\Command;
interface Application
{
    public function getName() : string;
    public function getVersion() : string;
    public function getLongVersion() : string;
    public function getHelp() : string;
    public function getCommands() : array;
    public function getDefaultCommand() : string;
    public function isAutoExitEnabled() : bool;
    public function areExceptionsCaught() : bool;
}
