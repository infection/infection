<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config\Guesser;

class SourceDirGuesser implements Guesser
{
    private $composerJsonContent;

    public function __construct(\stdClass $composerJsonContent)
    {
        $this->composerJsonContent = $composerJsonContent;
    }

    public function guess()
    {
        if (!isset($this->composerJsonContent->autoload)) {
            return null;
        }

        $autoload = $this->composerJsonContent->autoload;

        if (isset($autoload->{'psr-4'})) {
            return $this->getValues('psr-4');
        }

        if (isset($autoload->{'psr-0'})) {
            return $this->getValues('psr-0');
        }

        return null;
    }

    private function getValues(string $psr): array
    {
        $dirs = $this->parsePsrSection((array) $this->composerJsonContent->autoload->{$psr});

        // we don't want to mix different framework's folders like "app" for Symfony
        if (in_array('src', $dirs, true)) {
            return ['src'];
        }

        return $dirs;
    }

    private function parsePsrSection(array $autoloadDirs): array
    {
        $dirs = [];

        foreach ($autoloadDirs as $path) {
            if (!is_array($path) && !is_string($path)) {
                throw new \LogicException('autoload section does not match the expected JSON schema');
            }

            $this->parsePath($path, $dirs);
        }

        return $dirs;
    }

    /**
     * @param array|string $path
     * @param array        $dirs
     */
    private function parsePath($path, array &$dirs)
    {
        if (is_array($path)) {
            array_walk_recursive(
                $path,
                function ($el) use (&$dirs) {
                    $this->parsePath($el, $dirs);
                }
            );
        }

        if (is_string($path)) {
            $dirs[] = trim($path, DIRECTORY_SEPARATOR);
        }
    }
}
