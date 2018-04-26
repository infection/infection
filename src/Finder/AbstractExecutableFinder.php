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
        $path = getenv('PATH') ?: getenv('Path');

        if (!$path) {
            return null;
        }

        $dirs = array_merge(
            explode(PATH_SEPARATOR, $path),
            $extraDirectories
        );

        foreach ($dirs as $dir) {
            foreach ($probableNames as $name) {
                $fileName = sprintf('%s/%s', $dir, $name);

                if (file_exists($fileName)) {
                    return $fileName;
                }
            }
        }
    }
}
