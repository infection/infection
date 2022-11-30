<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console\Command;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\NotInstantiable;
use InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\RuntimeException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use function chdir as native_chdir;
use function file_exists;
use function _HumbugBoxb47773b41c19\Safe\getcwd;
use function sprintf;
final class ChangeableDirectory
{
    use NotInstantiable;
    private const WORKING_DIR_OPT = 'working-dir';
    public static function createOption() : InputOption
    {
        return new InputOption(self::WORKING_DIR_OPT, 'd', InputOption::VALUE_REQUIRED, 'If specified, use the given directory as working directory.', null);
    }
    public static function changeWorkingDirectory(IO $io) : void
    {
        $workingDir = $io->getOption(self::WORKING_DIR_OPT)->asNullableString();
        if (null === $workingDir) {
            return;
        }
        if (!file_exists($workingDir)) {
            throw new InvalidArgumentException(sprintf('Could not change the working directory to "%s": directory does not exists.', $workingDir));
        }
        if (!native_chdir($workingDir)) {
            throw new RuntimeException(sprintf('Failed to change the working directory to "%s" from "%s".', $workingDir, getcwd()));
        }
    }
}
