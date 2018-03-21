<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Finder;

abstract class AbstractExecutableFinder
{
    abstract public function find(): string;

    /**
     * @param array $probableNames
     * @param array $extraDirectories
     *
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
                    return $path;
                }
            }
        }
    }
}
