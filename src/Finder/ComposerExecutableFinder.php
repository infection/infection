<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Finder;

use Infection\Finder\Exception\FinderException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @internal
 */
final class ComposerExecutableFinder extends AbstractExecutableFinder
{
    public function find(): string
    {
        $probable = ['composer', 'composer.phar'];
        $finder = new ExecutableFinder();
        $immediatePaths = [getcwd(), realpath(getcwd() . '/../'), realpath(getcwd() . '/../../')];

        foreach ($probable as $name) {
            if ($path = $finder->find($name, null, $immediatePaths)) {
                if (false === strpos($path, '.phar')) {
                    return $path;
                }

                return $this->makeExecutable($path);
            }
        }

        /**
         * Check for options without execute permissions and prefix the PHP
         * executable instead.
         */
        $path = $this->searchNonExecutables($probable, $immediatePaths);

        if (null !== $path) {
            return $this->makeExecutable($path);
        }

        throw FinderException::composerNotFound();
    }

    private function makeExecutable(string $path): string
    {
        return sprintf(
            '%s %s',
            (new PhpExecutableFinder())->find(),
            $path
        );
    }
}
