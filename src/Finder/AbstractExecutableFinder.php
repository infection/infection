<?php

declare(strict_types=1);

namespace Infection\Finder;

use Symfony\Component\Process\PhpExecutableFinder;

abstract class AbstractExecutableFinder
{

    /**
     * @return string
     */
    abstract public function find();

    /**
     * @param array $probableNames
     * @param array $extraDirectories
     * @return string|null
     */
    protected function searchNonExecutables(array $probableNames, array $extraDirectories = [])
    {
        $dirs = array_merge(
            explode(PATH_SEPARATOR, getenv('PATH') ?: getenv('Path')),
            $extraDirectories
        );
        foreach ($dirs as $dir) {
            foreach ($probableNames as $name) {
                $path = sprintf('%s/%s', $dir, $name);
                if (file_exists($path)) {
                    return $this->makeExecutable($path);
                }
            }
        }
    }

    /**
     * @param string $path
     * @return string
     */
    protected function makeExecutable($path)
    {
        $phpFinder = new PhpExecutableFinder();
        return sprintf('%s %s', $phpFinder->find(), $path);
    }
}
