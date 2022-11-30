<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem\Finder;

use _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\Exception\FinderException;
use function _HumbugBox9658796bb9f0\Safe\getcwd;
use function _HumbugBox9658796bb9f0\Safe\realpath;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_contains;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\ExecutableFinder;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\PhpExecutableFinder;
final class ComposerExecutableFinder
{
    public function find() : string
    {
        $probable = ['composer', 'composer.phar'];
        $finder = new ExecutableFinder();
        $immediatePaths = [getcwd(), realpath(getcwd() . '/../'), realpath(getcwd() . '/../../')];
        foreach ($probable as $name) {
            $path = $finder->find($name, null, $immediatePaths);
            if ($path !== null) {
                if (!str_contains($path, '.phar')) {
                    return $path;
                }
                return $this->makeExecutable($path);
            }
        }
        $nonExecutableFinder = new NonExecutableFinder();
        $path = $nonExecutableFinder->searchNonExecutables($probable, $immediatePaths);
        if ($path !== null) {
            return $this->makeExecutable($path);
        }
        throw FinderException::composerNotFound();
    }
    private function makeExecutable(string $path) : string
    {
        return sprintf('%s %s', (new PhpExecutableFinder())->find(), $path);
    }
}
