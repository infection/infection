<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console\Command;

use function chdir;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use function getcwd;
use function sprintf;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\RuntimeException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class ChangeWorkingDirOption
{
    private const WORKING_DIR_OPT = 'working-dir';
    public static function getOptionInput() : InputOption
    {
        return new InputOption(self::WORKING_DIR_OPT, 'd', InputOption::VALUE_REQUIRED, 'If specified, use the given directory as working directory.', null);
    }
    public static function changeWorkingDirectory(IO $io) : void
    {
        $workingDir = $io->getOption(self::WORKING_DIR_OPT)->asNullableNonEmptyString();
        if (null === $workingDir) {
            return;
        }
        Assert::directory($workingDir, 'Could not change the working directory to "%s": directory does not exists or file is not a directory.');
        if (\false === chdir($workingDir)) {
            throw new RuntimeException(sprintf('Failed to change the working directory to "%s" from "%s".', $workingDir, getcwd()));
        }
    }
}
