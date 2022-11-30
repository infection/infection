<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Process;

class ExecutableFinder
{
    private $suffixes = ['.exe', '.bat', '.cmd', '.com'];
    public function setSuffixes(array $suffixes)
    {
        $this->suffixes = $suffixes;
    }
    public function addSuffix(string $suffix)
    {
        $this->suffixes[] = $suffix;
    }
    public function find(string $name, string $default = null, array $extraDirs = []) : ?string
    {
        if (\ini_get('open_basedir')) {
            $searchPath = \array_merge(\explode(\PATH_SEPARATOR, \ini_get('open_basedir')), $extraDirs);
            $dirs = [];
            foreach ($searchPath as $path) {
                if (@\is_dir($path)) {
                    $dirs[] = $path;
                } else {
                    if (\basename($path) == $name && @\is_executable($path)) {
                        return $path;
                    }
                }
            }
        } else {
            $dirs = \array_merge(\explode(\PATH_SEPARATOR, \getenv('PATH') ?: \getenv('Path')), $extraDirs);
        }
        $suffixes = [''];
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $pathExt = \getenv('PATHEXT');
            $suffixes = \array_merge($pathExt ? \explode(\PATH_SEPARATOR, $pathExt) : $this->suffixes, $suffixes);
        }
        foreach ($suffixes as $suffix) {
            foreach ($dirs as $dir) {
                if (@\is_file($file = $dir . \DIRECTORY_SEPARATOR . $name . $suffix) && ('\\' === \DIRECTORY_SEPARATOR || @\is_executable($file))) {
                    return $file;
                }
            }
        }
        return $default;
    }
}
