<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Config\Guesser;

use stdClass;
use function strpos;
use function trim;
final class PhpUnitPathGuesser
{
    private const CURRENT_DIR_PATH = '.';
    private stdClass $composerJsonContent;
    public function __construct(stdClass $composerJsonContent)
    {
        $this->composerJsonContent = $composerJsonContent;
    }
    public function guess() : string
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
    private function getPhpUnitDir(array $parsedPaths) : string
    {
        foreach ($parsedPaths as $namespace => $parsedPath) {
            if (strpos($namespace, 'SymfonyStandard') !== \false && trim($parsedPath, '/') === 'app') {
                return 'app';
            }
        }
        return self::CURRENT_DIR_PATH;
    }
}
