<?php

declare(strict_types=1);


namespace Infection\Finder;


class Locator
{
    /**
     * @var string
     */
    protected $projectPath;

    public function __construct(string $projectPath)
    {
        $this->projectPath = $projectPath;
    }

    public function locate($name)
    {
        if ($this->isAbsolutePath($name)) {
            if (!file_exists($name)) {
                throw new \Exception(sprintf('The file "%s" does not exist.', $name));
            }

            return realpath($name);
        }

        if (@file_exists($file = $this->projectPath . DIRECTORY_SEPARATOR . $name)) {
            return realpath($file);
        }

        throw new \Exception(sprintf('The file "%s" does not exist (in: %s).', $name, $this->projectPath));
    }

    public function locateDirectories($wildcard)
    {
        $directoryNames = glob($this->projectPath . '/' . $wildcard, GLOB_ONLYDIR);

        return array_map([$this, 'locate'], $directoryNames);
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return bool
     */
    private function isAbsolutePath($file)
    {
        return $file[0] === '/' || $file[0] === '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] === ':'
                && ($file[2] === '\\' || $file[2] === '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME);
    }
}