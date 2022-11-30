<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console;

use function array_key_exists;
use function in_array;
use _HumbugBox9658796bb9f0\Infection\CannotBeInstantiated;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
final class LogVerbosity
{
    use CannotBeInstantiated;
    public const DEBUG = 'all';
    public const NORMAL = 'default';
    public const NONE = 'none';
    public const DEBUG_INTEGER = 1;
    public const NORMAL_INTEGER = 2;
    public const NONE_INTEGER = 3;
    public const ALLOWED_OPTIONS = [self::DEBUG_INTEGER => self::DEBUG, self::NORMAL_INTEGER => self::NORMAL, self::NONE_INTEGER => self::NONE];
    public static function convertVerbosityLevel(InputInterface $input, ConsoleOutput $io) : void
    {
        $verbosityLevel = $input->getOption('log-verbosity');
        if (in_array($verbosityLevel, self::ALLOWED_OPTIONS, \true)) {
            return;
        }
        $verbosityLevel = (int) $verbosityLevel;
        if (array_key_exists($verbosityLevel, self::ALLOWED_OPTIONS)) {
            $input->setOption('log-verbosity', self::ALLOWED_OPTIONS[$verbosityLevel]);
            $io->logVerbosityDeprecationNotice(self::ALLOWED_OPTIONS[$verbosityLevel]);
            return;
        }
        $io->logUnknownVerbosityOption(self::NORMAL);
        $input->setOption('log-verbosity', self::NORMAL);
    }
}
