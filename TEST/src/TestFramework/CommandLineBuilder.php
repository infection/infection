<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

use function array_filter;
use function array_merge;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\Exception\FinderException;
use function is_executable;
use const PHP_SAPI;
use function _HumbugBox9658796bb9f0\Safe\substr;
use function shell_exec;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\PhpExecutableFinder;
class CommandLineBuilder
{
    private ?array $cachedPhpCmdLine = null;
    public function build(string $testFrameworkExecutable, array $phpExtraArgs, array $frameworkArgs) : array
    {
        if ($this->isBatchFile($testFrameworkExecutable)) {
            return array_merge([$testFrameworkExecutable], $frameworkArgs);
        }
        $phpExtraArgs = array_filter($phpExtraArgs);
        if ('cli' === PHP_SAPI && $phpExtraArgs === [] && is_executable($testFrameworkExecutable) && shell_exec('command -v php') !== null) {
            return array_merge([$testFrameworkExecutable], $frameworkArgs);
        }
        $commandLineArgs = array_merge($this->findPhp(), $phpExtraArgs, [$testFrameworkExecutable], $frameworkArgs);
        return array_filter($commandLineArgs);
    }
    private function findPhp() : array
    {
        $cachedPhpCmdLine = $this->cachedPhpCmdLine;
        if ($cachedPhpCmdLine !== null) {
            return $cachedPhpCmdLine;
        }
        $phpExec = (new PhpExecutableFinder())->find(\false);
        if ($phpExec === \false) {
            throw FinderException::phpExecutableNotFound();
        }
        $cachedPhpCmdLine[] = $phpExec;
        if (PHP_SAPI === 'phpdbg') {
            $cachedPhpCmdLine[] = '-qrr';
        }
        $this->cachedPhpCmdLine = $cachedPhpCmdLine;
        return $cachedPhpCmdLine;
    }
    private function isBatchFile(string $path) : bool
    {
        return substr($path, -4) === '.bat';
    }
}
