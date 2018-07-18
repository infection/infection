<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config\Guesser;

/**
 * @internal
 */
final class PhpUnitPathGuesser implements Guesser
{
    private const CURRENT_DIR_PATH = '.';

    private $composerJsonContent;

    public function __construct(\stdClass $composerJsonContent)
    {
        $this->composerJsonContent = $composerJsonContent;
    }

    public function guess()
    {
        if (!isset($this->composerJsonContent->autoload)) {
            return self::CURRENT_DIR_PATH;
        }

        $autoload = $this->composerJsonContent->autoload;

        if (isset($autoload->{'psr-4'})) {
            return $this->getPhpUnitDir((array) $autoload->{'psr-4'});
        }

        if (isset($autoload->{'psr-0'})) {
            return $this->getPhpUnitDir((array) $autoload->{'psr-0'});
        }

        return self::CURRENT_DIR_PATH;
    }

    private function getPhpUnitDir(array $parsedPaths)
    {
        foreach ($parsedPaths as $namespace => $parsedPath) {
            // for old Symfony prjects (<=2.7) phpunit.xml is located in ./app folder
            if (strpos($namespace, 'SymfonyStandard') !== false && trim($parsedPath, '/') === 'app') {
                return 'app';
            }
        }

        return self::CURRENT_DIR_PATH;
    }
}
